<?php

define('PATH_NO_IMAGE',  '/images/no_image.gif');
const ORDER_STATUS_SAVED = 'S';

/**
 * Requests usergroup for customer
 *
 * @param int $user_id User identifier
 * @param int $usergroup_id Usergroup identifier
 * @param string $type Type of request (join|cancel)
 * @return bool True if request successfuly sent, false otherwise
 */
function fn_request_usergroup($user_id, $usergroup_id, $type)
{
    $success = false;
    if (!empty($user_id)) {
        $_data = array(
            'user_id' => $user_id,
            'usergroup_id' => $usergroup_id,
        );

        if ($type == 'cancel') {
            $_data['status'] = 'F';

        } elseif ($type == 'join') {
            $_data['status'] = 'P';
            $success = true;
        }

        if (!empty($_data['status'])) {
            db_query("REPLACE INTO ?:usergroup_links SET ?u", $_data);
        }
    }

    return $success;
}


function fn_get_agent_by_id($id) {
    $agent = db_get_row('SELECT * FROM ?:users WHERE user_id = ?i', $id );

    return $agent;
}


/**
 * Add/update user
 *
 * @param int $user_id - user ID to update (empty for new user)
 * @param array $user_data - user data
 * @param array $auth - authentication information
 * @param bool $ship_to_another - flag indicates that shipping and billing fields are different
 * @param bool $notify_user - flag indicates that user should be notified
 * @param bool $send_password - TRUE if the password should be included into the e-mail
 * @return array with user ID and profile ID if success, false otherwise
 */
function fn_update_subagent ($user_id, $user_data, &$auth, $ship_to_another, $notify_user, $send_password = false)
{
    /**
     * Actions before updating user
     *
     * @param int   $user_id         User ID to update (empty for new user)
     * @param array $user_data       User data
     * @param array $auth            Authentication information
     * @param bool  $ship_to_another Flag indicates that shipping and billing fields are different
     * @param bool  $notify_user     Flag indicates that user should be notified
     * @param bool  $send_password   TRUE if the password should be included into the e-mail
     */
    fn_set_hook('update_user_pre', $user_id, $user_data, $auth, $ship_to_another, $notify_user, $send_password);

    $register_at_checkout = isset($user_data['register_at_checkout']) && $user_data['register_at_checkout'] == 'Y' ? true : false;

//    if (fn_allowed_for('ULTIMATE')) {
//        if (AREA == 'A' && !empty($user_data['user_type']) && $user_data['user_type'] == 'C' && (empty($user_data['company_id']) || (Registry::get('runtime.company_id') &&  $user_data['company_id'] != Registry::get('runtime.company_id')))) {
//            fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('access_denied'));
//
//            return false;
//        }
//    }

    if (!empty($user_id)) {
        $current_user_data = db_get_row("SELECT user_id, company_id, is_root, status, user_type, user_login, lang_code, password, salt, last_passwords FROM ?:users WHERE user_id = ?i", $user_id);

        if (empty($current_user_data)) {
            fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('object_not_found', array('[object]' => fn_get_lang_var('user'))),'','404');

            return false;
        }

//        if (fn_allowed_for('MULTIVENDOR')) {
        if (AREA != 'A') {
            //we should set company_id for the frontend
            $user_data['company_id'] = $current_user_data['company_id'];
        }
//        }

        $action = 'update';
    } else {
        $current_user_data = array(
            'status' => (AREA != 'A' && Registry::get('settings.General.approve_user_profiles') == 'Y') ? 'D' : (!empty($user_data['status']) ? $user_data['status'] : 'A'),
            'user_type' => 'P', // FIXME?
        );

        $action = 'add';

        $user_data['lang_code'] = !empty($user_data['lang_code']) ? $user_data['lang_code'] : CART_LANGUAGE;
        $user_data['timestamp'] = TIME;
    }

    $original_password = '';
    $current_user_data['password'] = !empty($current_user_data['password']) ? $current_user_data['password'] : '';
    $current_user_data['salt'] = !empty($current_user_data['salt']) ? $current_user_data['salt'] : '';

    // Set the user type
    $user_data['user_type'] = fn_check_user_type($user_data, $current_user_data);

    if (
        Registry::get('runtime.company_id')
//        && !fn_allowed_for('ULTIMATE')
        && (
            !fn_check_user_type_admin_area($user_data['user_type'])
            || (
                isset($current_user_data['company_id'])
                && $current_user_data['company_id'] != Registry::get('runtime.company_id')
            )
        )
    ) {
        fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('access_denied'));

        return false;
    }

    // Check if this user needs login/password
    if (fn_user_need_login($user_data['user_type'])) {
        // Check if user_login already exists
        // FIXME
        if (!isset($user_data['email'])) {
            $user_data['email'] = db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $user_id);
        }

        $is_exist = fn_is_user_exists($user_id, $user_data);

        if ($is_exist) {
            fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_user_exists'), '', 'user_exist');

            return false;
        }

        // Check the passwords
        if (!empty($user_data['password1']) || !empty($user_data['password2'])) {
            $original_password = trim($user_data['password1']);
            $user_data['password1'] = !empty($user_data['password1']) ? trim($user_data['password1']) : '';
            $user_data['password2'] = !empty($user_data['password2']) ? trim($user_data['password2']) : '';
        }

        // if the passwords are not set and this is not a forced password check
        // we will not update password, otherwise let's check password
        if (!empty($_SESSION['auth']['forced_password_change']) || !empty($user_data['password1']) || !empty($user_data['password2'])) {

            $valid_passwords = true;

            if ($user_data['password1'] != $user_data['password2']) {
                $valid_passwords = false;
                fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_passwords_dont_match'));
            }

            // PCI DSS Compliance
            if (fn_check_user_type_admin_area($user_data['user_type'])) {

                $msg = array();
                // Check password length
                $min_length = Registry::get('settings.Security.min_admin_password_length');
                if (strlen($user_data['password1']) < $min_length || strlen($user_data['password2']) < $min_length) {
                    $valid_passwords = false;
                    $msg[] = str_replace("[number]", $min_length, fn_get_lang_var('error_password_min_symbols'));
                }

                // Check password content
                if (Registry::get('settings.Security.admin_passwords_must_contain_mix') == 'Y') {
                    $tmp_result = preg_match('/\d+/', $user_data['password1']) && preg_match('/\D+/', $user_data['password1']) && preg_match('/\d+/', $user_data['password2']) && preg_match('/\D+/', $user_data['password2']);
                    if (!$tmp_result) {
                        $valid_passwords = false;
                        $msg[] = fn_get_lang_var('error_password_content');
                    }
                }

                if ($msg) {
                    fn_set_notification('E', fn_get_lang_var('error'), implode('<br />', $msg));
                }

                // Check last 4 passwords
                if (!empty($user_id)) {
                    $prev_passwords = !empty($current_user_data['last_passwords']) ? explode(',', $current_user_data['last_passwords']) : array();

                    if (!empty($_SESSION['auth']['forced_password_change'])) {
                        // if forced password change - new password can't be equal to current password.
                        $prev_passwords[] = $current_user_data['password'];
                    }

                    if (in_array(fn_generate_salted_password($user_data['password1'], $current_user_data['salt']), $prev_passwords)) {
                        $valid_passwords = false;
                        fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_password_was_used'));
                    } else {
                        if (count($prev_passwords) >= 5) {
                            array_shift($prev_passwords);
                        }
                        $user_data['last_passwords'] = implode(',', $prev_passwords);
                    }
                }
            } // PCI DSS Compliance

            if (!$valid_passwords) {
                return false;
            }

            $user_data['salt'] = fn_generate_salt();
            $user_data['password'] = fn_generate_salted_password($user_data['password1'], $user_data['salt']);
            if ($user_data['password'] != $current_user_data['password'] && !empty($user_id)) {
                // if user set current password - there is no necessity to update password_change_timestamp
                $user_data['password_change_timestamp'] = $_SESSION['auth']['password_change_timestamp'] = TIME;
            }
            unset($_SESSION['auth']['forced_password_change']);
            fn_delete_notification('password_expire');

        }
    }

    $user_data['status'] = (AREA != 'A' || empty($user_data['status'])) ? $current_user_data['status'] : $user_data['status']; // only administrator can change user status

    // Fill the firstname, lastname and phone from the billing address if the profile was created or updated through the admin area.
    if (AREA != 'A') {
        Registry::get('settings.General.address_position') == 'billing_first' ? $address_zone = 'b' : $address_zone = 's';
    } else {
        $address_zone = 'b';
    }
    if (!empty($user_data['firstname']) || !empty($user_data[$address_zone . '_firstname'])) {
        $user_data['firstname'] = empty($user_data['firstname']) && !empty($user_data[$address_zone . '_firstname']) ? $user_data[$address_zone . '_firstname'] : $user_data['firstname'];
    }
    if (!empty($user_data['lastname']) || !empty($user_data[$address_zone . '_lastname'])) {
        $user_data['lastname'] = empty($user_data['lastname']) && !empty($user_data[$address_zone . '_lastname']) ? $user_data[$address_zone . '_lastname'] : $user_data['lastname'];
    }
    if (!empty($user_data['phone']) || !empty($user_data[$address_zone . '_phone'])) {
        $user_data['phone'] = empty($user_data['phone']) && !empty($user_data[$address_zone . '_phone']) ? $user_data[$address_zone . '_phone'] : $user_data['phone'];
    }


    if (!empty($current_user_data['is_root']) && $current_user_data['is_root'] == 'Y') {
        $user_data['is_root'] = 'Y';
    } else {
        $user_data['is_root'] = 'N';
    }

    // check if it is a root admin
    $is_root_admin_exists = db_get_field(
        "SELECT user_id FROM ?:users WHERE company_id = ?i AND is_root = 'Y' AND user_id != ?i",
        $user_data['company_id'], !empty($user_id) ? $user_id : 0
    );
    $user_data['is_root'] = empty($is_root_admin_exists) && $user_data['user_type'] !== 'C' ? 'Y' : 'N';

    unset($user_data['user_id']);

    if (!empty($user_id)) {
        db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", $user_data, $user_id);

        fn_clean_usergroup_links($user_id, $current_user_data['user_type'], $user_data['user_type']);

        fn_log_event('users', 'update', array(
            'user_id' => $user_id,
        ));
    } else {
        if (!isset($user_data['password_change_timestamp'])) {
            $user_data['password_change_timestamp'] = 1;
        }
        $user_data['referrer_partner_id'] = $user_data['curator_id'];

        $user_id = db_query("INSERT INTO ?:users ?e" , $user_data);
//        db_query("UPDATE ?:aff_partner_profiles SET referrer_partner_id = ?i WHERE user_id = ?i ", array($user_data['curator_id'] , $user_id));

        fn_log_event('users', 'create', array(
            'user_id' => $user_id,
        ));
    }
    $user_data['user_id'] = $user_id;

    // Set/delete insecure password notification
    if (AREA == 'A' && Registry::get('config.demo_mode') != true && !empty($user_data['password1'])) {
        if (!fn_compare_login_password($user_data, $user_data['password1'])) {
            fn_delete_notification('insecure_password');
        } else {

            $lang_var = 'warning_insecure_password';
            if (Registry::get('settings.General.use_email_as_login') == 'Y') {
                $lang_var = 'warning_insecure_password_email';
            }

            fn_set_notification('E', fn_get_lang_var('warning'), fn_get_lang_var($lang_var, array(
                '[link]' => fn_url("profiles.update?user_id=" . $user_id)
            )), 'K', 'insecure_password');
        }
    }

    if (empty($user_data['user_login'])) { // if we're using email as login or user type does not require login, fill login field
        db_query("UPDATE ?:users SET user_login = 'user_?i' WHERE user_id = ?i AND user_login = ''", $user_id, $user_id);
    }

//    // Fill shipping info with billing if needed
//    if (empty($ship_to_another)) {
//        $profile_fields = fn_get_profile_fields($user_data['user_type']);
//        $use_default = (AREA == 'A') ? true : false;
//        fn_fill_address($user_data, $profile_fields, $use_default);
//    }


    if ($register_at_checkout) {
        $user_data['register_at_checkout'] = 'Y';
    }
    $lang_code = (AREA == 'A' && !empty($user_data['lang_code'])) ? $user_data['lang_code'] : CART_LANGUAGE;

//    if (!fn_allowed_for('ULTIMATE:FREE')) {
//        $user_data['usergroups'] = db_get_hash_array(
//            "SELECT lnk.link_id, lnk.usergroup_id, lnk.status, a.type, b.usergroup"
//            . " FROM ?:usergroup_links as lnk"
//            . " INNER JOIN ?:usergroups as a ON a.usergroup_id = lnk.usergroup_id AND a.status != 'D'"
//            . " LEFT JOIN ?:usergroup_descriptions as b ON b.usergroup_id = a.usergroup_id AND b.lang_code = ?s"
//            . " WHERE a.status = 'A' AND lnk.user_id = ?i AND lnk.status != 'D' AND lnk.status != 'F'"
//            , 'usergroup_id', $lang_code, $user_id
//        );
//    }

    // Send notifications to customer
    if (!empty($notify_user)) {
        $from =   Registry::get('settings.Company.company_users_department');

//        if (fn_allowed_for('MULTIVENDOR')) {
        // Vendor administrator's notification
        // is sent from root users department
        if ($user_data['user_type'] == 'V') {
            $from =   Registry::get('settings.Company.company_users_department');
        }
//        }

        // Notify customer about profile activation (when update profile only)
        if ($action == 'update' && $current_user_data['status'] === 'D' && $user_data['status'] === 'A') {
            Registry::get('view_mail')->assign( 'password' , $original_password);
            Registry::get('view_mail')->assign('send_password' , $send_password);
            Registry::get('view_mail')->assign('user_data' , $user_data);

            fn_send_mail($user_data['email'], $from, 'support/new_ticket_subj.tpl', 'profiles/profile_activated.tpl', $lang_code, '', true, $user_data['company_id'] );
        }

        // Notify customer about profile add/update
        $prefix = ($action == 'add') ? 'create' : 'update';
        Registry::get('view_mail')->assign('password', $original_password);
        Registry::get('view_mail')->assign('send_password' , $send_password);
        Registry::get('view_mail')->assign('user_data' , $user_data);
        fn_send_mail($user_data['email'], $from, 'support/new_ticket_subj.tpl', 'profiles/' . $prefix . '_profile.tpl', array(), $lang_code, '', true, $user_data['company_id']);

    }

    if ($action == 'add') {

        $skip_auth = true;
        if (AREA != 'A') {
            if (Registry::get('settings.General.approve_user_profiles') == 'Y') {
                fn_set_notification('W', fn_get_lang_var('important'), fn_get_lang_var('text_profile_should_be_approved'));

                // Notify administrator about new profile
                Registry::get('view_mail')->assign('user_data' , $user_data);
                fn_send_mail(Registry::get('settings.Company.company_users_department'), Registry::get('settings.Company.company_users_department'), 'support/new_ticket_subj.tpl', 'profiles/activate_profile.tpl', array(), $lang_code, $user_data['email'], true, $user_data['company_id']);


                $skip_auth = true;
            } else {
                fn_set_notification('N', fn_get_lang_var('information'), fn_get_lang_var('text_profile_is_created'));
            }
        }

        if (!is_null($auth)) {

            if (empty($skip_auth)) {
                $auth = fn_fill_auth($user_data);
            }
        }
    } else {
        if (AREA == 'C') {
            fn_set_notification('N', fn_get_lang_var('information'), fn_get_lang_var('text_profile_is_updated'));
        }
    }

    return array($user_id, !empty($user_data['profile_id']) ? $user_data['profile_id'] : false);

}

function fn_agents_get_products($params, $items_per_page = 0, $lang_code = CART_LANGUAGE, $need_images = true)
{
    /**
     * Changes params for selecting products
     *
     * @param array  $params         Product search params
     * @param int    $items_per_page Items per page
     * @param string $lang_code      Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_products_pre', $params, $items_per_page, $lang_code);

    // Init filter
//    $params = LastView::instance()->update('products', $params);

    // Set default values to input params
    $default_params = array (
        'area' => AREA,
        'extend' => array('product_name', 'prices', 'categories'),
        'custom_extend' => array(),
        'pname' => '',
        'pshort' => '',
        'pfull' => '',
        'pkeywords' => '',
        'feature' => array(),
        'type' => 'simple',
        'page' => 1,
        'action' => '',
        'variants' => array(),
        'ranges' => array(),
        'custom_range' => array(),
        'field_range' => array(),
        'features_hash' => '',
        'limit' => 0,
        'bid' => 0,
        'match' => '',
        'tracking' => array(),
        'get_frontend_urls' => false,
        'items_per_page' => $items_per_page
    );
    if (empty($params['custom_extend'])) {
        $params['extend'] = !empty($params['extend']) ? array_merge($default_params['extend'], $params['extend']) : $default_params['extend'];
    } else {
        $params['extend'] = $params['custom_extend'];
    }

    $params = array_merge($default_params, $params);

    if ((empty($params['pname']) || $params['pname'] != 'Y') && (empty($params['pshort']) || $params['pshort'] != 'Y') && (empty($params['pfull']) || $params['pfull'] != 'Y') && (empty($params['pkeywords']) || $params['pkeywords'] != 'Y') && (empty($params['feature']) || $params['feature'] != 'Y') && !empty($params['q'])) {
        $params['pname'] = 'Y';
    }

    $auth = & $_SESSION['auth'];

    // Define fields that should be retrieved
    if (empty($params['only_short_fields'])) {
        $fields = array (
            'products.*',
        );
    } else {
        $fields = array (
            'products.product_id',
            'products.product_code',
            'products.product_type',
            'products.status',
            'products.company_id',
            'products.list_price',
            'products.amount',
            'products.weight',
            'products.tracking',
            'products.is_edp',
        );
    }

    $companies_ids = array();
    if (!empty($params['filter_city'])) {
        $companies_offices = fn_agents_get_company_offices(null, array('city_id' => $params['filter_city']) );
        foreach($companies_offices as $c_office) {
            $companies_ids[$c_office['company_id']] = $c_office['company_id'];
        }
    }

//    $profit_field = db_process("IF(products.id = ?i,
//                            IF()
//                            (products.price*?f),
//                            IF(products.main_category IN (?n),
//                               (products.price*?f),
//                               (products.price*?f)
//                              )
//                            )");
    // Define sort fields
    $sortings = array (
        'code' => 'products.product_code',
        'status' => 'products.status',
        'product' => 'descr1.product',
        'position' => 'products_categories.position',
        'price' => 'price',
        'list_price' => 'products.list_price',
        'weight' => 'products.weight',
        'amount' => 'products.amount',
        'timestamp' => 'products.timestamp',
        'updated_timestamp' => 'products.updated_timestamp',
        'popularity' => 'popularity.total',
        'company' => 'company_name',
        'null' => 'NULL'
    );

    if (!empty($params['get_subscribers'])) {
        $sortings['num_subscr'] = 'num_subscr';
        $fields[] = 'COUNT(DISTINCT product_subscriptions.subscription_id) as num_subscr';
    }

    if (!empty($params['order_ids'])) {
        $sortings['p_qty'] = 'purchased_qty';
        $sortings['p_subtotal'] = 'purchased_subtotal';
        $fields[] = "SUM(?:order_details.amount) as purchased_qty";
        $fields[] = "SUM(?:order_details.price * ?:order_details.amount) as purchased_subtotal";
    }

    if (isset($params['compact']) && $params['compact'] == 'Y') {
        $union_condition = ' OR ';
    } else {
        $union_condition = ' AND ';
    }

    $join = $condition = $u_condition = $inventory_condition = '';
    $having = array();

    // Search string condition for SQL query
    if (isset($params['q']) && fn_string_not_empty($params['q'])) {

        $params['q'] = trim($params['q']);
        if ($params['match'] == 'any') {
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' OR ';
        } elseif ($params['match'] == 'all') {
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' AND ';
        } else {
            $pieces = array($params['q']);
            $search_type = '';
        }

        $_condition = array();
        foreach ($pieces as $piece) {
            if (strlen($piece) == 0) {
                continue;
            }

            $tmp = db_quote("(descr1.search_words LIKE ?l)", '%' . $piece . '%'); // check search words

            if ($params['pname'] == 'Y') {
                $tmp .= db_quote(" OR descr1.product LIKE ?l", '%' . $piece . '%');
            }
            if ($params['pshort'] == 'Y') {
                $tmp .= db_quote(" OR descr1.short_description LIKE ?l", '%' . $piece . '%');
                $tmp .= db_quote(" OR descr1.short_description LIKE ?l", '%' . htmlentities($piece, ENT_QUOTES, 'UTF-8') . '%');
            }
            if ($params['pfull'] == 'Y') {
                $tmp .= db_quote(" OR descr1.full_description LIKE ?l", '%' . $piece . '%');
                $tmp .= db_quote(" OR descr1.full_description LIKE ?l", '%' . htmlentities($piece, ENT_QUOTES, 'UTF-8') . '%');
            }
            if ($params['pkeywords'] == 'Y') {
                $tmp .= db_quote(" OR (descr1.meta_keywords LIKE ?l OR descr1.meta_description LIKE ?l)", '%' . $piece . '%', '%' . $piece . '%');
            }
            if (!empty($params['feature']) && $params['action'] != 'feature_search') {
                $tmp .= db_quote(" OR ?:product_features_values.value LIKE ?l", '%' . $piece . '%');
            }

            fn_set_hook('additional_fields_in_search', $params, $fields, $sortings, $condition, $join, $sorting, $group_by, $tmp, $piece, $having);

            $_condition[] = '(' . $tmp . ')';
        }

        $_cond = implode($search_type, $_condition);

        if (!empty($_condition)) {
            $condition .= ' AND (' . $_cond . ') ';
        }

        if (!empty($params['feature']) && $params['action'] != 'feature_search') {
            $join .= " LEFT JOIN ?:product_features_values ON ?:product_features_values.product_id = products.product_id";
            $condition .= db_quote(" AND (?:product_features_values.feature_id IN (?n) OR ?:product_features_values.feature_id IS NULL)", array_values($params['feature']));
        }

        //if perform search we also get additional fields
        if ($params['pname'] == 'Y') {
            $params['extend'][] = 'product_name';
        }

        if ($params['pshort'] == 'Y' || $params['pfull'] == 'Y' || $params['pkeywords'] == 'Y') {
            $params['extend'][] = 'description';
        }

        unset($_condition);
    }

    //
    // [Advanced and feature filters]
    //

    //Сортировака...
    if(!empty($params['sort_name']) ) {
        if (strtolower($params['sort_name']) != 'desc'){
            $order[] = db_quote(' product asc');
        }   else    {
            $order[] = db_quote('product desc');
        }
    }

    if(!empty($params['sort_price']) ) {
        if (strtolower($params['sort_price']) != 'desc'){
            $order[] = db_quote(' price asc');
        }   else    {
            $order[] = db_quote(' price desc');
        }
    }


    if (!empty($params['variants'])) {
        $params['features_hash'] .= implode('.', $params['variants']);
    }

    // Feature code
    if (!empty($params['feature_code'])) {
        $join .= db_quote(" LEFT JOIN ?:product_features ON ?:product_features_values.feature_id = ?:product_features.feature_id");
        $condition .= db_quote(" AND ?:product_features.feature_code = ?s", $params['feature_code']);
    }

    $advanced_variant_ids = $simple_variant_ids = $ranges_ids = $fields_ids = $fields_ids_revert = $slider_vals = array();

    if (!empty($params['features_hash'])) {
        list($av_ids, $ranges_ids, $fields_ids, $slider_vals, $fields_ids_revert) = fn_parse_features_hash($params['features_hash']);
        $advanced_variant_ids = db_get_hash_multi_array("SELECT feature_id, variant_id FROM ?:product_feature_variants WHERE variant_id IN (?n)", array('feature_id', 'variant_id'), $av_ids);
    }

    if (!empty($params['multiple_variants'])) {
        $simple_variant_ids = $params['multiple_variants'];
    }

    if (!empty($advanced_variant_ids)) {
        $join .= db_quote(" LEFT JOIN (SELECT product_id, GROUP_CONCAT(?:product_features_values.variant_id) AS advanced_variants FROM ?:product_features_values WHERE lang_code = ?s GROUP BY product_id) AS pfv_advanced ON pfv_advanced.product_id = products.product_id", $lang_code);

        $where_and_conditions = array();
        foreach ($advanced_variant_ids as $k => $variant_ids) {
            $where_or_conditions = array();
            foreach ($variant_ids as $variant_id => $v) {
                $where_or_conditions[] = db_quote(" FIND_IN_SET('?i', advanced_variants)", $variant_id);
            }
            $where_and_conditions[] = '(' . implode(' OR ', $where_or_conditions) . ')';
        }
        $condition .= ' AND ' . implode(' AND ', $where_and_conditions);
    }

    if (!empty($simple_variant_ids)) {
        $join .= db_quote(" LEFT JOIN (SELECT product_id, GROUP_CONCAT(?:product_features_values.variant_id) AS simple_variants FROM ?:product_features_values WHERE lang_code = ?s GROUP BY product_id) AS pfv_simple ON pfv_simple.product_id = products.product_id", $lang_code);

        $where_conditions = array();
        foreach ($simple_variant_ids as $k => $variant_id) {
            $where_conditions[] = db_quote(" FIND_IN_SET('?i', simple_variants)", $variant_id);
        }
        $condition .= ' AND ' . implode(' AND ', $where_conditions);
    }

    //
    // Ranges from text inputs
    //

    // Feature ranges
    if (!empty($params['custom_range'])) {
        foreach ($params['custom_range'] as $k => $v) {
            $k = intval($k);
            if (isset($v['from']) && fn_string_not_empty($v['from']) || isset($v['to']) && fn_string_not_empty($v['to'])) {
                if (!empty($v['type'])) {
                    if ($v['type'] == 'D') {
                        $v['from'] = fn_parse_date($v['from']);
                        $v['to'] = fn_parse_date($v['to']);
                    }
                }
                $join .= db_quote(" LEFT JOIN ?:product_features_values as custom_range_$k ON custom_range_$k.product_id = products.product_id AND custom_range_$k.lang_code = ?s", $lang_code);
                if (fn_string_not_empty($v['from']) && fn_string_not_empty($v['to'])) {
                    $condition .= db_quote(" AND (custom_range_$k.value_int >= ?i AND custom_range_$k.value_int <= ?i AND custom_range_$k.value = '' AND custom_range_$k.feature_id = ?i) ", $v['from'], $v['to'], $k);
                } else {
                    $condition .= " AND custom_range_$k.value_int" . (fn_string_not_empty($v['from']) ? db_quote(' >= ?i', $v['from']) : db_quote(" <= ?i AND custom_range_$k.value = '' AND custom_range_$k.feature_id = ?i ", $v['to'], $k));
                }
            }
        }
    }
    // Product field ranges
    $filter_fields = fn_get_product_filter_fields();
    if (!empty($params['field_range'])) {
        foreach ($params['field_range'] as $field_type => $v) {
            $structure = $filter_fields[$field_type];
            if (!empty($structure) && (!empty($v['from']) || !empty($v['to']))) {
                if ($field_type == 'P') { // price
                    $v['cur'] = !empty($v['cur']) ? $v['cur'] : CART_SECONDARY_CURRENCY;
                    if (empty($v['orig_cur'])) {
                        // saving the first user-entered values
                        // will be always search by it
                        $v['orig_from'] = $v['from'];
                        $v['orig_to'] = $v['to'];
                        $v['orig_cur'] = $v['cur'];
                        $params['field_range'][$field_type] = $v;
                    }
                    if ($v['orig_cur'] != CART_PRIMARY_CURRENCY) {
                        // calc price in primary currency
                        $cur_prim_coef  = Registry::get('currencies.' . $v['orig_cur'] . '.coefficient');
                        $decimals = Registry::get('currencies.' . CART_PRIMARY_CURRENCY . '.decimals');
                        $search_from = round($v['orig_from'] * floatval($cur_prim_coef), $decimals);
                        $search_to = round($v['orig_to'] * floatval($cur_prim_coef), $decimals);
                    } else {
                        $search_from = $v['orig_from'];
                        $search_to = $v['orig_to'];
                    }
                    // if user switch the currency, calc new values for displaying in filter
                    if ($v['cur'] != CART_SECONDARY_CURRENCY) {
                        if (CART_SECONDARY_CURRENCY == $v['orig_cur']) {
                            $v['from'] = $v['orig_from'];
                            $v['to'] = $v['orig_to'];
                        } else {
                            $prev_coef = Registry::get('currencies.' . $v['cur'] . '.coefficient');
                            $cur_coef  = Registry::get('currencies.' . CART_SECONDARY_CURRENCY . '.coefficient');
                            $v['from'] = floor(floatval($v['from']) * floatval($prev_coef) / floatval($cur_coef));
                            $v['to'] = ceil(floatval($v['to']) * floatval($prev_coef) / floatval($cur_coef));
                        }
                        $v['cur'] = CART_SECONDARY_CURRENCY;
                        $params['field_range'][$field_type] = $v;
                    }
                }

                $params["$structure[db_field]_from"] = trim(isset($search_from) ? $search_from : $v['from']);
                $params["$structure[db_field]_to"] = trim(isset($search_to) ? $search_to : $v['to']);
            }
        }
    }
    // Ranges from database
    if (!empty($ranges_ids)) {
        $filter_conditions = db_get_hash_multi_array("SELECT `from`, `to`, feature_id, filter_id, range_id FROM ?:product_filter_ranges WHERE range_id IN (?n)", array('filter_id', 'range_id'), $ranges_ids);
        $where_conditions = array();
        foreach ($filter_conditions as $fid => $range_conditions) {
            foreach ($range_conditions as $k => $range_condition) {
                $k = $fid . "_" . $k;
                $join .= db_quote(" LEFT JOIN ?:product_features_values as var_val_$k ON var_val_$k.product_id = products.product_id AND var_val_$k.lang_code = ?s", $lang_code);
                $where_conditions[] = db_quote("(var_val_$k.value_int >= ?i AND var_val_$k.value_int <= ?i AND var_val_$k.value = '' AND var_val_$k.feature_id = ?i)", $range_condition['from'], $range_condition['to'], $range_condition['feature_id']);
            }
            $condition .= db_quote(" AND (?p)", implode(" OR ", $where_conditions));
            $where_conditions = array();
        }
    }

    // Field ranges
    //$fields_ids = empty($params['fields_ids']) ? $fields_ids : $params['fields_ids'];
    if (!empty($params['fields_ids'])) {

        foreach ($fields_ids as $rid => $field_type) {
            if (!empty($filter_fields[$field_type])) {
                $structure = $filter_fields[$field_type];
                if ($structure['condition_type'] == 'D' && empty($structure['slider'])) {
                    $range_condition = db_get_row("SELECT `from`, `to`, range_id FROM ?:product_filter_ranges WHERE range_id = ?i", $rid);
                    if (!empty($range_condition)) {
                        $params["$structure[db_field]_from"] = $range_condition['from'];
                        $params["$structure[db_field]_to"] = $range_condition['to'];
                    }
                } elseif ($structure['condition_type'] == 'F') {
                    $params['filter_params'][$structure['db_field']][] = $rid;
                } elseif ($structure['condition_type'] == 'C') {
                    $params['filter_params'][$structure['db_field']][] = ($rid == 1) ? 'Y' : 'N';
                }
            }
        }
    } elseif (!empty($fields_ids_revert)) {
        foreach ($fields_ids_revert as $field_type => $rids) {
            if (!empty($filter_fields[$field_type])) {
                $structure = $filter_fields[$field_type];
                if ($structure['condition_type'] == 'D' && empty($structure['slider'])) {
                    foreach ($rids as $rid) {
                        $range_condition = db_get_row("SELECT `from`, `to`, range_id FROM ?:product_filter_ranges WHERE range_id = ?i", $rid);
                        if (!empty($range_condition)) {
                            $params["$structure[db_field]_from"] = $range_condition['from'];
                            $params["$structure[db_field]_to"] = $range_condition['to'];
                        }
                    }
                } elseif ($structure['condition_type'] == 'F') {
                    $params['filter_params'][$structure['db_field']] = $rids;
                } elseif ($structure['condition_type'] == 'C') {
                    if (count($rids) > 1) {
                        foreach ($rids as $rid) {
                            if ($fields_ids[$rid] == $field_type) {
                                unset($fields_ids[$rid]);
                            }
                            $params['features_hash'] = fn_delete_range_from_url($params['features_hash'], array('range_id' => $rid), $field_type);
                        }
                    } else {
                        $params['filter_params'][$structure['db_field']][] = ($rids[0] == 1) ? 'Y' : 'N';
                    }
                }
            }
        }
    }

    // Slider ranges
    $slider_vals = empty($params['slider_vals']) ? $slider_vals : $params['slider_vals'];
    if (!empty($slider_vals)) {
        foreach ($slider_vals as $field_type => $vals) {
            if (!empty($filter_fields[$field_type])) {
                if ($field_type == 'P') {
                    $currency = !empty($vals[2]) ? $vals[2] : CART_PRIMARY_CURRENCY;
                    if ($currency != CART_PRIMARY_CURRENCY) {
                        $coef = Registry::get('currencies.' . $currency . '.coefficient');
                        $decimals = Registry::get('currencies.' . CART_PRIMARY_CURRENCY . '.decimals');
                        $vals[0] = round(floatval($vals[0]) * floatval($coef), $decimals);
                        $vals[1] = round(floatval($vals[1]) * floatval($coef), $decimals);
                    }
                }

                $structure = $filter_fields[$field_type];
                $params["$structure[db_field]_from"] = $vals[0];
                $params["$structure[db_field]_to"] = $vals[1];
            }
        }
    }

    // Checkbox features
    if (!empty($params['ch_filters']) && !fn_is_empty($params['ch_filters'])) {
        foreach ($params['ch_filters'] as $k => $v) {
            // Product field filter
            if (is_string($k) == true && !empty($v) && $structure = $filter_fields[$k]) {
                $condition .= db_quote(" AND $structure[table].$structure[db_field] IN (?a)", ($v == 'A' ? array('Y', 'N') : $v));
                // Feature filter
            } elseif (!empty($v)) {
                $fid = intval($k);
                $join .= db_quote(" LEFT JOIN ?:product_features_values as ch_features_$fid ON ch_features_$fid.product_id = products.product_id AND ch_features_$fid.lang_code = ?s", $lang_code);
                $condition .= db_quote(" AND ch_features_$fid.feature_id = ?i AND ch_features_$fid.value IN (?a)", $fid, ($v == 'A' ? array('Y', 'N') : $v));
            }
        }
    }

    // Text features
    if (!empty($params['tx_features'])) {
        foreach ($params['tx_features'] as $k => $v) {
            if (fn_string_not_empty($v)) {
                $fid = intval($k);
                $join .= " LEFT JOIN ?:product_features_values as tx_features_$fid ON tx_features_$fid.product_id = products.product_id";
                $condition .= db_quote(" AND tx_features_$fid.value LIKE ?l AND tx_features_$fid.lang_code = ?s", "%" . trim($v) . "%", $lang_code);
            }
        }
    }

    $total = 0;
    fn_set_hook('get_products_before_select', $params, $join, $condition, $u_condition, $inventory_condition, $sortings, $total, $items_per_page, $lang_code, $having);

    //
    // [/Advanced filters]
    //

    $feature_search_condition = '';
    if (!empty($params['feature'])) {
        // Extended search by product fields
        $_cond = array();
        $total_hits = 0;
        foreach ($params['feature'] as $f_id) {
            if (!empty($f_val)) {
                $total_hits++;
                $_cond[] = db_quote("(?:product_features_values.feature_id = ?i)", $f_id);
            }
        }

        $params['extend'][] = 'categories';
        if (!empty($_cond)) {
            $cache_feature_search = db_get_fields("SELECT product_id, COUNT(product_id) as cnt FROM ?:product_features_values WHERE (" . implode(' OR ', $_cond) . ") GROUP BY product_id HAVING cnt = $total_hits");
            $feature_search_condition .= db_quote(" AND products_categories.product_id IN (?n)", $cache_feature_search);
        }
    }

    // Category search condition for SQL query
    if (!empty($params['cid'])) {
        $cids = is_array($params['cid']) ? $params['cid'] : explode(',', $params['cid']);

        if (!empty($params['subcats']) && $params['subcats'] == 'Y') {
            $_ids = db_get_fields("SELECT a.category_id FROM ?:categories as a LEFT JOIN ?:categories as b ON b.category_id IN (?n) WHERE a.id_path LIKE CONCAT(b.id_path, '/%')", $cids);

            $cids = fn_array_merge($cids, $_ids, false);
        }

        $params['extend'][] = 'categories';
        $condition .= db_quote(" AND ?:categories.category_id IN (?n)", $cids);
    }

    // If we need to get the products by IDs and no IDs passed, don't search anything
    if (!empty($params['force_get_by_ids']) && empty($params['pid']) && empty($params['product_id'])) {
        return array(array(), $params, 0);
    }

    // Product ID search condition for SQL query
    if (!empty($params['pid'])) {
        $u_condition .= db_quote($union_condition . ' products.product_id IN (?n)', $params['pid']);
    }

    // Exclude products from search results
    if (!empty($params['exclude_pid'])) {
        $condition .= db_quote(' AND products.product_id NOT IN (?n)', $params['exclude_pid']);
    }

    // Search by feature comparison flag
    if (!empty($params['feature_comparison'])) {
        $condition .= db_quote(' AND products.feature_comparison = ?s', $params['feature_comparison']);
    }

    // Search products by localization
    $condition .= fn_get_localizations_condition('products.localization', true);

    $company_condition = '';

////    if (fn_allowed_for('MULTIVENDOR')) {
//        if ($params['area'] == 'C') {
//            $company_condition .= " AND companies.status = 'A' ";
//            $params['extend'][] = 'companies';
//        } else {
//            $company_condition .= fn_get_company_condition('products.company_id');
//        }
////    } else {
//        $cat_company_condition = '';
//        if (Registry::get('runtime.company_id')) {
//            $params['extend'][] = 'categories';
//            $cat_company_condition .= fn_get_company_condition('?:categories.company_id');
//        } elseif (!empty($params['company_ids'])) {
//            $params['extend'][] = 'categories';
//            $cat_company_condition .= db_quote(' AND ?:categories.company_id IN (?a)', explode(',', $params['company_ids']));
//        }
//        $company_condition .= $cat_company_condition;
//    }

    $condition .= $company_condition;

//    if (!fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id') && isset($params['company_id'])) {
//        $params['company_id'] = Registry::get('runtime.company_id');
//    }
    if (isset($params['company_id']) && $params['company_id'] != '') {
        $condition .= db_quote(' AND products.company_id = ?i ', $params['company_id']);
    }
    if (isset($params['client']['company']) && $params['client']['company'] != '') {
        $condition .= db_quote(' AND products.company_id = ?i ', $params['client']['company']);
    }
    if (!empty ($companies_ids)) {
        $condition .= db_quote(' AND products.company_id IN (?n) ', $companies_ids);
    }
    if (isset($params['product_id']) && $params['product_id'] != '') {

        $condition .= db_quote(' AND products.product_id = ?i ', $params['product_id']);
    }

    if (isset($params['client']['product']) && $params['client']['product'] != '') {
        $condition .= db_quote(' AND products.product_id = ?i ', $params['client']['product']);
    }
    $where = '';
    if (!empty($params['where'])) {
        foreach ($params['where'] as $field=>$value) {
            if (!empty ($value) ) {
                if($field == 'not') {
                    foreach ($value as $not_field => $not_value) {
                        if(in_array($not_field , array( 'product_id', 'company_id' ))) {
                            $not_field = db_process('products.'.$not_field);
                        }
                        $where .= db_process(" AND $not_field NOT IN (?a)", array($not_value));
                    }
                } else {
                    if(in_array($field , array( 'product_id', 'company_id' ))) {
                        $field = db_process('products.'.$field);
                    }
                    $where .= db_process(" AND $field IN (?a)", array($value));
                }
            }
        }
    }

    if(!empty($params['new'])) {
        $two_weeks = strtotime('-4 weeks');
        $where .= db_process( ' AND products.timestamp >=  ?i', array($two_weeks) );
    }

    $condition .= $where;


    if (!empty($params['filter_params'])) {
        foreach ($params['filter_params'] as $field => $f_vals) {
            $condition .= db_quote(' AND products.' . $field . ' IN (?a) ', $f_vals);
        }
    }

    if (isset($params['price_from']) && fn_is_numeric($params['price_from'])) {
        $condition .= db_quote(' AND prices.price >= ?d', fn_convert_price(trim($params['price_from'])));
        $params['extend'][] = 'prices2';
    }

    if (isset($params['price_to']) && fn_is_numeric($params['price_to'])) {
        $condition .= db_quote(' AND prices.price <= ?d', fn_convert_price(trim($params['price_to'])));
        $params['extend'][] = 'prices2';
    }



    // search specific inventory status
    if (!empty($params['tracking'])) {
        $condition .= db_quote(' AND products.tracking IN(?a)', $params['tracking']);
    }

    if (isset($params['amount_from']) && fn_is_numeric($params['amount_from'])) {
        $condition .= db_quote(" AND IF(products.tracking = 'O', inventory.amount >= ?i, products.amount >= ?i)", $params['amount_from'], $params['amount_from']);
        $inventory_condition .= db_quote(' AND inventory.amount >= ?i', $params['amount_from']);
    }

    if (isset($params['amount_to']) && fn_is_numeric($params['amount_to'])) {
        $condition .= db_quote(" AND IF(products.tracking = 'O', inventory.amount <= ?i, products.amount <= ?i)", $params['amount_to'], $params['amount_to']);
        $inventory_condition .= db_quote(' AND inventory.amount <= ?i', $params['amount_to']);
    }

    if (Registry::get('settings.General.inventory_tracking') == 'Y' && Registry::get('settings.General.show_out_of_stock_products') == 'N' && $params['area'] == 'C') { // FIXME? Registry in model
        $condition .= " AND IF(products.tracking = 'O', inventory.amount > 0, products.amount > 0)";
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND products.status IN (?a)', $params['status']);
    }

    if (!empty($params['shipping_freight_from'])) {
        $condition .= db_quote(' AND products.shipping_freight >= ?d', $params['shipping_freight_from']);
    }

    if (!empty($params['shipping_freight_to'])) {
        $condition .= db_quote(' AND products.shipping_freight <= ?d', $params['shipping_freight_to']);
    }

    if (!empty($params['free_shipping'])) {
        $condition .= db_quote(' AND products.free_shipping = ?s', $params['free_shipping']);
    }

    if (!empty($params['downloadable'])) {
        $condition .= db_quote(' AND products.is_edp = ?s', $params['downloadable']);
    }

    if (isset($params['pcode']) && fn_string_not_empty($params['pcode'])) {
        $pcode = trim($params['pcode']);
        $fields[] = 'inventory.combination';
        $u_condition .= db_quote(" $union_condition (inventory.product_code LIKE ?l OR products.product_code LIKE ?l)", "%$pcode%", "%$pcode%");
        $inventory_condition .= db_quote(" AND inventory.product_code LIKE ?l", "%$pcode%");
    }

    if ((isset($params['amount_to']) && fn_is_numeric($params['amount_to'])) || (isset($params['amount_from']) && fn_is_numeric($params['amount_from'])) || !empty($params['pcode']) || (Registry::get('settings.General.inventory_tracking') == 'Y' && Registry::get('settings.General.show_out_of_stock_products') == 'N' && $params['area'] == 'C')) {
        $join .= " LEFT JOIN ?:product_options_inventory as inventory ON inventory.product_id = products.product_id $inventory_condition";
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition .= db_quote(" AND (products.timestamp >= ?i AND products.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(" AND products.product_id IN (?n)", explode(',', $params['item_ids']));
    }

    if (isset($params['popularity_from']) && fn_is_numeric($params['popularity_from'])) {
        $params['extend'][] = 'popularity';
        $condition .= db_quote(' AND popularity.total >= ?i', $params['popularity_from']);
    }

    if (isset($params['popularity_to']) && fn_is_numeric($params['popularity_to'])) {
        $params['extend'][] = 'popularity';
        $condition .= db_quote(' AND popularity.total <= ?i', $params['popularity_to']);
    }

    if (!empty($params['order_ids'])) {
        $arr = (strpos($params['order_ids'], ',') !== false || !is_array($params['order_ids'])) ? explode(',', $params['order_ids']) : $params['order_ids'];

        $condition .= db_quote(" AND ?:order_details.order_id IN (?n)", $arr);

        $join .= " LEFT JOIN ?:order_details ON ?:order_details.product_id = products.product_id";
    }

    $limit = '';
    $group_by = 'products.product_id';
    // Show enabled products
    $_p_statuses = array('A');
    $condition .= ($params['area'] == 'C') ? ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'products.usergroup_ids', true) . ')' . db_quote(' AND products.status IN (?a)', $_p_statuses) : '';

    // -- JOINS --
    if (in_array('product_name', $params['extend'])) {
        $fields[] = 'descr1.product as product';
        $join .= db_quote(" LEFT JOIN ?:product_descriptions as descr1 ON descr1.product_id = products.product_id AND descr1.lang_code = ?s ", $lang_code);
    }

    // get prices
    $price_condition = '';
    if (in_array('prices', $params['extend'])) {
        $fields[] = 'MIN(IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100)) as price';
        $join .= " LEFT JOIN ?:product_prices as prices ON prices.product_id = products.product_id AND prices.lower_limit = 1";
        $price_condition = db_quote(' AND prices.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
        $condition .= $price_condition;
    }

    // get prices for search by price
    if (in_array('prices2', $params['extend'])) {
        $price_usergroup_cond_2 = db_quote(' AND prices_2.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
        $join .= " LEFT JOIN ?:product_prices as prices_2 ON prices.product_id = prices_2.product_id AND prices_2.lower_limit = 1 AND prices_2.price < prices.price " . $price_usergroup_cond_2;
        $condition .= ' AND prices_2.price IS NULL';
        $price_condition .= ' AND prices_2.price IS NULL';
    }

    // get short & full description
    if (in_array('search_words', $params['extend'])) {
        $fields[] = 'descr1.search_words';
    }

    // get short & full description
    if (in_array('description', $params['extend'])) {
        $fields[] = 'descr1.short_description';
        $fields[] = "IF(descr1.short_description = '', descr1.full_description, '') as full_description";
    }

    // get companies
    $companies_join = db_quote(" LEFT JOIN ?:companies AS companies ON companies.company_id = products.company_id ");
    if (in_array('companies', $params['extend'])) {
        $fields[] = 'companies.company as company_name';
        $join .= $companies_join;
    }

    // for compatibility
    if (in_array('category_ids', $params['extend'])) {
        $params['extend'][] = 'categories';
    }

    // get categories
    $_c_statuses = array('A' , 'H');// Show enabled categories
    $skip_checking_usergroup_permissions = fn_is_preview_action($auth, $params);

    if ($skip_checking_usergroup_permissions) {
        $category_avail_cond = '';
    } else {
        $category_avail_cond = ($params['area'] == 'C') ? ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], '?:categories.usergroup_ids', true) . ')' : '';
    }
    $category_avail_cond .= ($params['area'] == 'C') ? db_quote(" AND ?:categories.status IN (?a) ", $_c_statuses) : '';
    $categories_join = " INNER JOIN ?:products_categories as products_categories ON products_categories.product_id = products.product_id INNER JOIN ?:categories ON ?:categories.category_id = products_categories.category_id $category_avail_cond $feature_search_condition";

    if (!empty($params['order_ids'])) {
        // Avoid duplicating by sub-categories
        $condition .= db_quote(' AND products_categories.link_type = ?s', 'M');
    }

    if (in_array('categories', $params['extend'])) {
        $fields[] = "GROUP_CONCAT(IF(products_categories.link_type = 'M', CONCAT(products_categories.category_id, 'M'), products_categories.category_id)) as category_ids";
        $fields[] = 'products_categories.position';
        $join .= $categories_join;

        $condition .= fn_get_localizations_condition('?:categories.localization', true);
    }

    // get popularity
    $popularity_join = db_quote(" LEFT JOIN ?:product_popularity as popularity ON popularity.product_id = products.product_id");
    if (in_array('popularity', $params['extend'])) {
        $fields[] = 'popularity.total as popularity';
        $join .= $popularity_join;
    }

    if (!empty($params['get_subscribers'])) {
        $join .= " LEFT JOIN ?:product_subscriptions as product_subscriptions ON product_subscriptions.product_id = products.product_id";
    }

    //  -- \JOINs --

    if (!empty($u_condition)) {
        $condition .= " $union_condition ((" . ($union_condition == ' OR ' ? '0 ' : '1 ') . $u_condition . ')' . $company_condition . $price_condition . ')';
    }

    /**
     * Changes additional params for selecting products
     *
     * @param array  $params    Product search params
     * @param array  $fields    List of fields for retrieving
     * @param array  $sortings  Sorting fields
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $sorting   String containing the SQL-query ORDER BY clause
     * @param string $group_by  String containing the SQL-query GROUP BY field
     * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_products', $params, $fields, $sortings, $condition, $join, $sorting, $group_by, $lang_code, $having);

    // -- SORTINGS --
    if (empty($params['sort_by']) || empty($sortings[$params['sort_by']])) {
        $params = array_merge($params, fn_get_default_products_sorting());
        if (empty($sortings[$params['sort_by']])) {
            $_products_sortings = fn_get_products_sorting(false);
            $params['sort_by'] = key($_products_sortings);
        }
    }

    $default_sorting = fn_get_products_sorting(false);

    if ($params['sort_by'] == 'popularity' && !in_array('popularity', $params['extend'])) {
        $join .= $popularity_join;
    }

    if ($params['sort_by'] == 'company' && !in_array('companies', $params['extend'])) {
        $join .= $companies_join;
    }

    if (empty($params['sort_order'])) {
        if (!empty($default_sorting[$params['sort_by']]['default_order'])) {
            $params['sort_order'] = $default_sorting[$params['sort_by']]['default_order'];
        } else {
            $params['sort_order'] = 'asc';
        }
    }

//    $sorting = db_sort($params, $sortings);

//    if (fn_allowed_for('ULTIMATE')) {
//        if (in_array('sharing', $params['extend'])) {
//            $fields[] = "IF(COUNT(IF(?:categories.company_id = products.company_id, NULL, ?:categories.company_id)), 'Y', 'N') as is_shared_product";
//            if (strpos($join, $categories_join) === false) {
//                $join .= $categories_join;
//            }
//        }
//    }

    // -- \SORTINGS --

    // Used for View cascading
    if (!empty($params['get_query'])) {
        return "SELECT products.product_id FROM ?:products as products $join WHERE 1 $condition GROUP BY products.product_id";
    }

    // Used for Extended search
    if (!empty($params['get_conditions'])) {
        return array($fields, $join, $condition);
    }

    if (!empty($params['items_per_page'])) {
        $limit = fn_paginate($params['page'], $params['items_per_page']);
    }

    if (empty($params['sort_profit']) && !empty($params['limit']) ) {
        $limit = 'LIMIT ';
        if(!empty($params['page'])) {
            $limit .= ($params['page'] - 1) * $params['limit'] . ',' . $params['limit'];
        } else {
            $limit .= $params['limit'];
        }
        $limit .= ' ';
    } else {
        $limit = '';
    }

    $calc_found_rows = '';
    if (empty($total)) {
        $calc_found_rows = 'SQL_CALC_FOUND_ROWS';
    }

    if (!empty($having)) {
        $having = ' HAVING ' . implode(' AND ', $having);
    } else {
        $having = '';
    }

    //TODO check valid processing
    if (!empty ($order) && is_array($order)) {
        $order = 'ORDER BY ' . implode (', ', $order);
    } else {
        $order = ' ORDER BY product asc ';
    }
//    define('DEBUG_QUERIES', true);

    $products = db_get_array(db_process("SELECT $calc_found_rows " . implode(', ', $fields) . " FROM ?:products as products $join WHERE 1 $condition  GROUP BY $group_by $having  $sorting $order $limit") );

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = !empty($total)? $total : db_get_found_rows();
    } else {
        $params['total_items'] = count($products);
    }

    // Post processing
    if (in_array('categories', $params['extend'])) {
        foreach ($products as $k => $v) {
            list($products[$k]['category_ids'], $products[$k]['main_category']) = fn_convert_categories($v['category_ids']);
        }
    }
    if ($need_images) {
        add_images_to_products($products);

    }

    if (!empty($params['get_frontend_urls'])) {
        foreach ($products as &$product) {
            $product['url'] = fn_url('products.view?product_id=' . $product['product_id'], 'C');
        }
    }

    if (!empty($params['item_ids'])) {
        $products = fn_sort_by_ids($products, explode(',', $params['item_ids']));
    }
    if (!empty($params['pid']) && !empty($params['apply_limit']) && $params['apply_limit']) {
        $products = fn_sort_by_ids($products, $params['pid']);
    }


    /**
     * Changes selected products
     *
     * @param array  $products  Array of products
     * @param array  $params    Product search params
     * @param string $lang_code Language code
     */
    fn_set_hook('get_products_post', $products, $params, $lang_code);

//    LastView::instance()->processResults('products', $products, $params);

    return array($products, $params);
}


function add_images_to_products(&$products, $groupById = true) {
    if (empty($products)) {
        return;
    }
    $productsIds = array();
    $idToProduct = array();
    foreach($products as $k => $product) {
        $productsIds[] = $product['product_id'];
        $idToProduct[$product['product_id']] = $k;
    }
    $images = db_get_array('SELECT il.object_id, il.detailed_id, i.* FROM cscart_images_links il left JOIN cscart_images i ON i.image_id = il.detailed_id WHERE il.object_id in ('. implode(',' , $productsIds).') AND object_type = "product" ' . ($groupById? ' GROUP by object_id' : '') );

    foreach($images as $image) {
        $products[$idToProduct[$image['object_id']]]['image'] = $image;
    }
}

function get_images_for_product(&$product, $groupById = true) {

    $images = db_get_array('SELECT il.object_id, il.detailed_id, i.* FROM cscart_images_links il left JOIN cscart_images i ON i.image_id = il.detailed_id WHERE il.object_id = '. $product['product_id'] .' AND object_type = "product" ' . ($groupById? ' GROUP by object_id' : '') );

    $product['image'] = $groupById? $images[0] : $images;
    return $images;
}

function fn_agents_process_order($order_data, $step = 1, $auth) {
    if (empty($_SESSION['cart'])) {
        fn_clear_cart($_SESSION['cart']);
    }

    $cart = &$_SESSION['cart'];
    $_partner_data = fn_agents_add_affiliate_data_to_cart($cart, $auth);

    $product_id = $order_data['product_id'];
    $amount = $order_data['item_count'];

    if ($step == 2) {
        $locations = fn_agents_get_city_details($order_data['client']['city']);
        foreach(array('city', 'region' , 'address', 'country') as $field) {
            $order_data['client'][$field] = $locations[$field];
        }
        $client_id = fn_agents_register_customer($order_data['client']);
        $client = fn_agents_get_clients($auth['user_id'], array('where' => array('profile_id' => $client_id) ) );
        $client = $client[0];
        fn_clear_cart($cart);
        fn_agents_assign_client_to_cart($client, $cart);

        $company_office = fn_agents_get_company_offices(null, array('office_id' => $order_data['client']['office']));
        $company_office = $company_office[0];
        fn_agents_assign_company_office_to_cart($company_office, $cart);
        $product_data = array($product_id => array('product_id' => $product_id, 'amount' => $amount) );
        fn_add_product_to_cart($product_data, $cart, $auth);
        fn_save_cart_content($cart, $auth['user_id']);
        fn_calculate_cart_content($cart, $auth, 'S', true, 'F', true);
    }

    if($step == 3) {
        $order_id = fn_agents_new_order($cart, $auth);
        $order_id = $order_id[0];
        fn_clear_cart($cart);
        fn_save_cart_content($cart, $auth['user_id']);
        $from_email = array(
            'email' => Registry::get('settings.Company.company_support_department'),
            'name' => 'Mail system'
        );
        Registry::get('view_mail')->assign('target', 'agent');

        if ( !empty($order_data['order_filepath']) ) {
            $uploadfile = $order_data['order_filepath'];
        } else {
            $uploadfile = '';
        }

        $agent = fn_get_user_info($auth['user_id']);
        $company = fn_get_company_data($order_data['client']['company']);
        $to_email = $agent['email'];
        Registry::get('view_mail')->assign('order_id', $order_id);
        Registry::get('view_mail')->assign('target', 'agent');
        fn_send_mail($to_email, $from_email,'agents/new_order_subj.tpl', 'agents/new_order_message.tpl', $uploadfile ? array('attachment' => $uploadfile) : array(), CART_LANGUAGE);
        $to_email = $company['email'];
        Registry::get('view_mail')->assign('target', 'company');
        fn_send_mail($to_email, $from_email,'agents/new_order_subj.tpl', 'agents/new_order_message.tpl', $uploadfile ? array('attachment' => $uploadfile) : array(), CART_LANGUAGE);
        if(!empty($uploadfile)) {
            fn_delete_file($uploadfile);
        }

    }


}


function fn_agents_register_customer ($customer_data) {
    $action = 'add';
    $user_id = $customer_data['affiliate_id'];
    $firstname = explode(' ', $customer_data['fio'])[1];
    $lastname = explode(' ', $customer_data['fio'])[0];
    $customer_data['office'] = fn_agents_get_company_offices(null, array('office_id' => $customer_data['office']));
    $customer_data['office'] = $customer_data['office'][0];
    $customer_data['address'] = $customer_data['office']['address'];
    $existing_client = db_get_array(
        db_process(
'SELECT * FROM ?:user_profiles WHERE
    profile_name = ?s
    AND profile_type = "S"
    AND b_phone = ?s
    AND b_email = ?s
    AND b_country = ?s
    AND b_state = ?s
    AND b_city = ?s
    AND b_address = ?s
    AND user_id = ?i',
        array(
            $customer_data['fio'],
            $customer_data['phone'],
            $customer_data['email'],
            $customer_data['country'],
            $customer_data['region'],
            $customer_data['city'],
            $customer_data['address'],
            $user_id,
        )
    ));
    if(is_array($existing_client) && count($existing_client) >= 1 ) {
        $existing_client = $existing_client[0];
        if(!empty($customer_data['comment'])) {
            db_query(db_process('UPDATE ?:user_profiles SET comment = ?s WHERE user_id = ?i AND profile_id = ?i') , array($customer_data['comment'], $existing_client['user_id'], $existing_client['profile_id']) );
        }
        return $existing_client['profile_id'];
    }

    $user_data = array(
        'user_id' => $user_id,
        'profile_name' => $customer_data['fio'],
        'profile_type' => 'S',
        'b_firstname' => $firstname,
        's_firstname' => $firstname,
        'b_lastname' => $lastname,
        's_lastname' => $lastname,
        'b_phone' => $customer_data['phone'],
        's_phone' => $customer_data['phone'],
        'b_email' => $customer_data['email'],
        's_email' => $customer_data['email'],
        'comment' => $customer_data['comment'],
        'registartion_date' => TIME,
        'b_city' =>  $customer_data['city'],
        's_city' =>  $customer_data['city'],
        'b_state' =>  $customer_data['region'],
        's_state' =>  $customer_data['region'],
        'b_country' =>  $customer_data['country'],
        's_country' =>  $customer_data['country'],
        'b_address' =>  $customer_data['address'],
        's_address' =>  $customer_data['office'],
    );
    fn_set_hook('update_user_profile_pre', $user_id, $user_data, $action);

    $user_data['profile_id'] = db_query("INSERT INTO ?:user_profiles ?e", $user_data);

    // Add/Update additional fields
    fn_store_profile_fields($user_data, array('U' => $user_id, 'P' => $user_data['profile_id']), 'UP');

    /**
     * Perform actions after user profile update
     *
     * @param int $user_id User identifier
     * @param array $user_data Profile information
     * @param string $action Current action (Example: 'add')
     */
    fn_set_hook('update_user_profile_post', $user_id, $user_data, $action);

    return $user_data['profile_id'];


}

function fn_agents_new_order(&$cart, &$auth, $action = '', $parent_order_id = 0) {
    $order_id =fn_place_order($cart, $auth, $action, $parent_order_id);
    if(!empty($order_id) && !empty($order_id[0])) {
        fn_set_notification('N', fn_get_lang_var('congratulations'), fn_get_lang_var('text_order_saved_successfully'));
    } else {
        fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('text_order_placed_error'));
    }
    return $order_id;
}
function fn_agents_save_order(&$cart, &$auth, $action = '', $parent_order_id = 0) {
    $order_id = fn_place_order($cart, $auth, $action, $parent_order_id);
    $order_id = $order_id[0];
    if($order_id) {
        db_query(db_process('UPDATE ?:orders SET status = "?s" WHERE order_id = ?i', array(ORDER_STATUS_SAVED, $order_id)) );
        db_query(db_process('INSERT INTO ?:orders_saved (`id`, `order_id`, `user_id`) VALUES (NULL, ?i, ?i)', array($order_id, $auth['user_id']) ) );
    }
    return $order_id;

}

function fn_agents_get_clients($user_id, $params) {
    $select = 'SELECT * ';
    $from = db_quote('FROM cscart_user_profiles ');
    $where = db_quote(' WHERE user_id = ?i AND profile_type = ?s', $user_id, PROFILE_TYPE_CLIENT);
    $group = '';
    $order = ' ORDER BY profile_name ASC ';
    ///FILTERS
    if(!empty($params['where']) ) {
        foreach($params['where'] as $field => $value) {
            $where .= db_process(" AND $field IN (?a) ", array($value));
        }
    }

    $limit = '';
    if (!empty($params['limit']) ) {
        $limit = 'LIMIT ';
        if(!empty($params['page'])) {
            $limit .= ($params['page'] - 1) * $params['limit'] . ',' . $params['limit'];
        } else {
            $limit .= $params['limit'];
        }
        $limit .= ' ';
    } else {
        $limit = '';
    }

    $clients = db_get_array($select . $from . $where . $group  . $order . $limit);
    if(empty($clients)) {
        $clients = array();
    }
    return $clients;
}

function fn_agents_get_client_fields_errors($client, $params = array()) {
    $field_types = array(
        'not_empty_fields',
        'email_fields',
        'integer_fields'
    );
    $not_empty_fields = array(
        'affiliate_id',
        'fio',
        'phone'
    );
    $email_fields = array(
        'email'
    );
    $integer_fields = array(
        'affiliate_id',
        'phone'
    );
    foreach ($field_types as $field_type) {
        if(isset($params[$field_type])) {
            if(!empty($params[$field_type]['default'])) {
                unset ($params[$field_type]['default']);
                $$field_type = array_merge($$field_type, $params[$field_type]);
            } else {
                $$field_type = $params[$field_type];
            }
        }
    }


    if(isset($params['email_fields'])) {
        $email_fields = $params['email_fields'];
    }

    if(isset($params['integer_fields'])) {
        $integer_fields = $params['integer_fields'];
    }
    $errors = array();

    foreach ($not_empty_fields as $not_empty) {
        if(empty($client[$not_empty]) ) {
            $errors[$not_empty][] = 'is_empty';
        }
    }

    foreach ($email_fields as $email) {
        if(!fn_validate_email($client[$email])) {
            $errors[$email][] = 'invalid_email';
        }
    }

    foreach ($integer_fields as $numeric) {
        if (!is_numeric($client[$numeric])) {
            $errors[$numeric][] = 'invalid_value';
        }
    }

    return $errors;
}


function fn_agents_get_field_errors($field, $value) {
    $not_empty_fields = array(
        'affiliate_id',
        'fio',
        'phone',
        'b_phone',
        'profile_name'
    );
    $email_fields = array(
        'email',
        'b_email'
    );
    $integer_fields = array(
        'affiliate_id',
        'phone',
        'b_phone'
    );
    $errors = array();
    if(in_array($field,  $not_empty_fields) ) {
        if(empty($value)) {
            $errors[] = 'is_empty';
        }
    }
    if(in_array($field,  $integer_fields) ) {
        $value = intval($value);
        if(empty($value)) {
            $errors[] = 'invalid_value';
        }
    }
    if(in_array($field,  $email_fields) ) {
        if(!fn_validate_email($value)) {
            $errors[] = 'invalid_email';
        }
    }

    return $errors;
}

function fn_agents_display_errors($errors){
    foreach ($errors as $field=>$field_errors) {
        foreach($field_errors as $error) {
            $error_text = fn_get_lang_var('field') . ' "' . fn_get_lang_var($field) . '" ' . fn_get_lang_var($error);
            fn_set_notification('E', fn_get_lang_var('error'), $error_text);
        }
    }
}

function fn_agents_get_orders($user_id, $params = array(), $lang_code = CART_LANGUAGE) {
    $_params = array(
        'limit'     => '20',
        'page'      => '0',
        'where'     => '',
        'group'     => '',
        'order'     => array('order_id DESC')
    );

    foreach($_params as $key => $value) {
        if(isset($params[$key])) {
            $_params[$key] = $params[$key];
        }
    }
    $select = db_process('SELECT ?:orders.*, ?:order_details.order_id AS oid, ?:order_details.product_id, ?:order_details.extra ');
    $from = db_process('FROM ?:orders ');
    $join = db_process('JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id ');
    $where = empty($user_id) ? '' : db_process('WHERE user_id = ?i ', array($user_id) );

    if (!empty($_params['where'])) {
        foreach ($_params['where'] as $field=>$value) {
            if (!empty ($value) ) {
                if($field == 'not') {
                    foreach ($value as $not_field => $not_value) {
                        if($not_field == 'order_id') {
                            $not_field = db_process('?:orders.order_id');
                        }
                        $where .= db_process(" AND $not_field NOT IN (?a)", array($not_value));
                    }
                } else {
                    if($field == 'order_id') {
                        $field = db_process('?:orders.order_id');
                    }
                    $where .= db_process(" AND $field IN (?a)", array($value));
                }
            }
        }
    }

    if (!empty($_params['group']) ) {
        if (!is_array($_params['group'])) {
            $_params['group'] = array($_params['group']);
        }
        $group = 'GROUP BY ' . implode(',', $_params['group']);
    } else {
        $group = '';
    }

    if (!empty($_params['order']) ) {
        if (!is_array($_params['order'])) {
            $_params['order'] = array($_params['order']);
        }
        $order = array();
        foreach ($_params['order'] as $field=>$value) {
            if (!empty ($value) ) {
                if(!is_numeric($field)) {
                    $order[] = db_process("$field $value ");
                } else {
                    $order[] = db_process(" $value ");
                }
            }
        }
        $order = !empty($order) && is_array($order) ?  'ORDER BY ' . implode(',', $order) : '';
    } else {
        $order = ' ORDER BY order_id DESC';
    }

    if (!empty($_params['limit']) ) {
        $limit = 'LIMIT ';
        if(!empty($_params['page'])) {
            $limit .= ($_params['page'] - 1) * $_params['limit'] . ',' . $_params['limit'];
        } else {
            $limit .= $_params['limit'];
        }
        $limit .= ' ';
    } else {
        $limit = '';
    }

    $query_string = "$select  $from $join $where  $group  $order  $limit";

    $agent_orders = db_get_array($query_string);
    $status_descriptions = array();
    $agent_plan = fn_get_affiliate_plan_data_by_partner_id($user_id);
    foreach ($agent_orders as &$order) {
        $order['product_data'] = unserialize($order['extra']);
        $order['product_data']['product_id'] = $order['product_id'];
        $order['product_data']['description'] = fn_agents_get_product_description($order['product_id']);
        get_images_for_product($order['product_data']);
        $order['product_data']['image']['image_path'] = get_image_full_path($order['product_data']['image'] /*$order['product_data']['company_id']*/);
        $order['company_data'] = fn_agents_get_company_info($order['company_id']);
        $order['company_data']['image_path'] = get_image_full_path($order['company_data'] );
        if (empty($status_descriptions[$order['status']]) ) {
            $status_descriptions[$order['status']] = fn_agents_get_order_statuses($order['status']);
            $status_descriptions[$order['status']] = $status_descriptions[$order['status']][0]['description'];
        }
        $order['status_description'] = $status_descriptions[$order['status']];
        $order['product_data']['profit'] = fn_agents_get_plan_product_profit($agent_plan, $order['product_data']);

    }


    return $agent_orders;

}

function fn_agents_get_saved_orders($user_id, $params = array(), $lang_code = CART_LANGUAGE) {
    $saved_orders = db_get_array(db_process('SELECT order_id FROM ?:orders_saved WHERE user_id = ?i', array($user_id) ));
    if (empty($saved_orders)) {
        return array();
    }
    $saved_orders_ids = array();
    foreach($saved_orders as $order) {
        $saved_orders_ids[] = $order['order_id'];
    }

    $params['where'][ db_process('?:orders.order_id') ] = $saved_orders_ids;
    return fn_agents_get_orders($user_id, $params, $lang_code);
}


function fn_agents_get_active_orders($user_id, $params = array(), $lang_code = CART_LANGUAGE) {
    $active_statuses = array('F', 'P');
    $params['where'][db_process('?:orders.status')] = $active_statuses ;
    return fn_agents_get_orders($user_id, $params, $lang_code);
}

function fn_agents_get_closed_orders($user_id, $params = array(), $lang_code = CART_LANGUAGE) {
    $closed_statuses = array('C');
    $params['where'][ db_process('?:orders.status')] = $closed_statuses;
    return fn_agents_get_orders($user_id, $params, $lang_code);
}

function fn_agents_get_order_statuses($status = null, $lang_code = CART_LANGUAGE) {

    $query = db_process('SELECT status, description, email_subj, email_header, lang_code FROM ?:status_descriptions WHERE lang_code = ?s AND type = "O"', array($lang_code));
    if(!empty($status)) {
        if (!is_array($status)) {
            $query .= db_process(' AND status = ?s', array($status));
        } else {
            $query .= db_process(' AND status IN (?a)', array($status));
        }
    }
    $query .= 'AND status != "B"';
    $statuses = db_get_array($query);
    return $statuses;
}

function fn_agents_get_active_order_statuses($lang_code = CART_LANGUAGE) {
    return fn_agents_get_order_statuses(array('O', 'P',  'A'), $lang_code);
}

function fn_agents_get_company_info($company_id, $lang_code = CART_LANGUAGE) {
    $query = db_process('SELECT c.company_id, c.company, c.email, c.fax, c.company_long_description, c.company_home_master, c.company_home_master_description, c.company_contract_id,  cd.company_description, c.phone, c.url, c.email, il.image_id, i.* FROM  ?:companies c LEFT JOIN ?:company_descriptions cd ON cd.company_id = c.company_id  LEFT JOIN ?:images_links il ON il.object_id = c.company_id LEFT JOIN ?:images i ON i.image_id = il.image_id WHERE c.company_id = ?i GROUP by c.company_id', array($company_id));
    $company_info = db_get_array($query);
    if(empty($company_info) ) {
        return array();
    } else {
        return $company_info[0] ;
    }

}

function fn_agents_get_product_description($product_id, $lang_code = CART_LANGUAGE) {
    $description = db_get_field(db_process('SELECT full_description FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s', array($product_id, $lang_code) ) );
    return $description;
}

function fn_agents_get_collegues($user_id, $params = array()) {
    $order = 'ORDER BY lastname ASC';
    $limit = '';
    $query = db_process('SELECT *, timestamp as registration_date FROM ?:users WHERE curator_id = ?i AND user_type = "P" ' . $order . $limit, array($user_id) );
    $collegues = db_get_array($query);
    return $collegues;
}


function fn_generate_guest_password() {
    return substr(md5(time()), 0, 8);
}

function fn_restore_processed_user_password(&$destination, &$source)
{
    $fields = array(
        'password', 'password1', 'password2'
    );

    foreach ($fields as $field) {
        if (isset($source[$field])) {
            $destination[$field] = $source[$field];
        }
    }
}

function get_image_full_path($image, $company_id=0, $prefered_x = 150, $prefered_y = 150) {
    $thumbnails_name = str_replace('_02.', '_01.', $image['image_path']);
    if(is_file(DIR_ROOT . "/images/thumbnails/$company_id/$prefered_x/$prefered_y/" . $thumbnails_name)) {
        return "/images/thumbnails/$company_id/$prefered_x/$prefered_y/" . $thumbnails_name;
    }
    if (!empty($image['image_id'])) {
        $sizes = array(150, 160, 320, 85,  50,  40,  30);
        foreach ($sizes as $size) {
            if(is_file(DIR_ROOT . "/images/thumbnails/$company_id/$size/$size/" . $thumbnails_name)) {
                return "/images/thumbnails/$company_id/$size/$size/" . $thumbnails_name;
            }
        }
        if (is_file(DIR_ROOT . "/images/thumbnails/$company_id/".$image['image_x']. '/' . $image['image_y'] .'/' . $thumbnails_name)) {
            return "/images/thumbnails/$company_id/".$image['image_x']. '/' . $image['image_y'] .'/' . $thumbnails_name;
        }
    }
    if(!empty($image['detailed_id']) ) {
        return "/images/detailed/$company_id/".$image['image_path'];
    }
    return PATH_NO_IMAGE;
}

function fn_agents_assign_client_to_cart($client, &$cart) {
    $udata = &$cart['user_data'];
    foreach($client as $field => $value) {
//        if(array_key_exists($field, $udata)) {
            $udata[$field] = $value;
//        }
    }
}

function fn_agents_assign_company_office_to_cart($office, &$cart) {
    $udata = &$cart['user_data'];
    foreach($office as $field => $value) {
        if(array_key_exists('s_'.$field, $udata)) {
            $udata['s_'.$field] = $value;
        }
    }
}
function fn_agents_add_affiliate_data_to_cart(&$cart, $auth) {
    $cart['affiliate']['code'] = empty($auth['user_id']) ? '' : fn_dec2any($auth['user_id']);
    $_partner_id = $auth['user_id'];
    $_partner_data = db_get_row("SELECT firstname, lastname, user_id as partner_id FROM ?:users WHERE user_id = ?i AND user_type = 'P'", $_partner_id);
    if (!empty($_partner_data)) {
        $cart['affiliate'] = $_partner_data + $cart['affiliate'];
        $_SESSION['partner_data'] = array(
            'partner_id' => $cart['affiliate']['partner_id'],
            'is_payouts' => 'N'
        );
    } else {
        unset($cart['affiliate']['partner_id']);
        unset($cart['affiliate']['firstname']);
        unset($cart['affiliate']['lastname']);
        unset($_SESSION['partner_data']);
    }
    return $_partner_data;
}

function fn_agents_get_all_cities($params = array(), $lang = CART_LANGUAGE, $full_select = false) {
    if(!empty($params['company_id']) ) {
        $company_offices = fn_agents_get_company_offices($params['company_id'], $params);
        $cities = fn_agents_extract_cities_from_offices($company_offices, false);
        return $cities;
    }
    if ($full_select) {
        $select = 'SELECT c.*, cl.* ';
    } else {
        $select = 'SELECT c.CityId, c.CountryId, c.RegionId, cl.name ';
    }
    $query = $select. db_process(' FROM Cities c LEFT JOIN Cities_lang cl ON cl.parent_id = c.CityId WHERE cl.lang_id = ?s AND c.CountryID = 203 ', array($lang) );
    if (!empty($params['region'])) {
        $query .= db_process(' AND RegionID = ?i', array($params['region']));
    }
    if (!empty($params['city_id'])) {
        if (is_array ($params['city_id']) ) {
            $query .= db_process(' AND c.CityId IN (?a)', array($params['city_id']) );
        } else {
            $query .= db_process(' AND CityId = ?i', array($params['city_id']));
        }
    }
    $query .= ' ORDER BY cl.name ASC';
    $cities = db_get_array($query);
//    foreach($cities as &$city) {
//        $city['city_id'] = $city['CityId'];
//        $city['region_id'] = $city['RegionId'];
//        $city['city_id'] = $city['CityId'];
//        $city['city'] = $city['name'];
//    }
//    unset ($city);
    return $cities;
}

function fn_agents_get_all_regions($lang = CART_LANGUAGE) {
    $query = db_process('SELECT r.*, rl.*  FROM Regions r LEFT JOIN Regions_lang rl ON rl.parent_id = r.RegionID WHERE rl.lang_id = ?s AND r.CountryId = 203 GROUP BY rl.name', array($lang) );
    $query .= ' ORDER BY rl.name ASC';

    $regions = db_get_array($query);
    return $regions;
}

function fn_agents_get_company_offices($company_id, $params = array(), $lang = CART_LANGUAGE) {
    $select = db_process('SELECT co.*, cl.name AS city FROM ?:company_offices co LEFT JOIN Cities_lang cl ON cl.parent_id = co.city_id ');
    $join = '';
    $company_condition = empty($company_id) ? '' : db_process('co.company_id = ?i AND', $company_id );
    $where = ' WHERE ' . $company_condition . db_process(' cl.lang_id = ?s ', array( $lang) );
    $order = ' ORDER BY city ASC ';

    if(!empty($params['office_id'])) {
        if (is_array($params['office_id'])) {
            $where .= db_process(' AND co.office_id IN (?n) ', array($params['office_id']) );
        } else {
            $where .= db_process(' AND co.office_id = ?i ', array($params['office_id']) );
        }
    }
    if(!empty($params['region_id'])) {
        $join .= db_process(' LEFT JOIN Cities c ON c.CityId = co.city_id');
        $where .= db_process(' AND c.RegionID = ?i ' , array($params['region_id']) );
    }
    if(!empty($params['city_id'])) {
        $where .= db_process(' AND co.city_id = ?i ' , array($params['city_id']) );
    }
    if(!empty($params['city'])) {
        $where .= db_process(' AND cl.city LIKE %?s% ' , array($params['city']) );
    }

    $offices = db_get_array($select . $join .  $where . $order);
    return $offices;
}

function fn_agents_get_company_offices_with_regions($company_id, $params = array(), $lang = CART_LANGUAGE) {
    $select = db_process('SELECT co.*, cl.name AS city,  rl.parent_id AS region_id, rl.name AS region FROM ?:company_offices co LEFT JOIN Cities c ON c.CityId = co.city_id LEFT JOIN Regions_lang rl ON rl.parent_id = c.RegionID LEFT JOIN Cities_lang cl ON cl.parent_id = co.city_id ');
    $join = '';
    $company_condition = empty($company_id) ? '' : db_process('co.company_id = ?i AND', $company_id );
    $where = ' WHERE ' . $company_condition . db_process(' rl.lang_id = ?s AND cl.lang_id = ?s ', array( $lang, $lang) );
    $order = ' ORDER BY city ASC ';

    if(!empty($params['office_id'])) {
        if (is_array($params['office_id'])) {
            $where .= db_process(' AND co.office_id IN (?n) ', array($params['office_id']) );
        } else {
            $where .= db_process(' AND co.office_id = ?i ', array($params['office_id']) );
        }
    }
    if(!empty($params['region_id'])) {
        $where .= db_process(' AND c.RegionID = ?i ' , array($params['region_id']) );
    }
    if(!empty($params['city_id'])) {
        $where .= db_process(' AND co.city_id = ?i ' , array($params['city_id']) );
    }
    if(!empty($params['region'])) {
        $where .= db_process(' AND rl.region LIKE %?s% ' , array($params['region']) );
    }

    $offices = db_get_array($select . $join .  $where . $order);
    return $offices;
}

function fn_agents_get_company_offices_with_shippings($company_id, $params = array(), $lang = CART_LANGUAGE) {
    $offices = fn_agents_get_company_offices($company_id, $params, $lang );
    foreach($offices as &$office) {
        $office['shippings'] = fn_agents_get_company_office_shippings($office['office_id'], $params, $lang);
    }
    unset($office);
    return $offices;
}

function fn_agents_get_fields_errors($params, $fields) {
    $not_empty_fields = $params['not_empty_fields'];
    $email_fields = $params['email_fields'];
    $integer_fields = $params['integer_fields'];
    $errors = array();
    foreach ($not_empty_fields as $not_empty) {
        if(empty($fields[$not_empty]) ) {
            $errors[$not_empty][] = 'is_empty';        }    }

    foreach ($email_fields as $email) {
        if(!fn_validate_email($fields[$email])) {
            $errors[$email][] = 'invalid_email';        }    }

    foreach ($integer_fields as $numeric) {
        if (!is_numeric($fields[$numeric])) {
            $errors[$numeric][] = 'invalid_value';        }    }

    return $errors;
}

function fn_agents_get_office_fields_errors($office) {
    $not_empty_fields = array(
        'office_name',
        'email',
        'phone',
        'city_id',
        'address'
    );
    if(isset($params['not_empty_fields'])) {
        $not_empty_fields = $params['not_empty_fields'];
    }
    $email_fields = array(
        'email'
    );
    if(isset($params['email_fields'])) {
        $email_fields = $params['email_fields'];
    }
    $integer_fields = array(
        'company_id',
//        'phone',
        'city_id'
    );
    if(isset($params['integer_fields'])) {
        $integer_fields = $params['integer_fields'];
    }
    $errors = array();

    foreach ($not_empty_fields as $not_empty) {
        if(empty($office[$not_empty]) ) {
            $errors[$not_empty][] = 'is_empty';
        }
    }

    foreach ($email_fields as $email) {
        if(!fn_validate_email($office[$email])) {
            $errors[$email][] = 'invalid_email';
        }
    }

    foreach ($integer_fields as $numeric) {
        if (isset($office[$numeric]) && !is_numeric($office[$numeric]) ) {
            $errors[$numeric][] = 'invalid_value';
        }
    }

    return $errors;
}
function fn_agents_get_shipping_fields_errors($shipping) {
    $rules = array(
        'not_empty_fields' => array('shipping_name'),
        'integer_fields' => array('office_id')
    );
    $errors = fn_agents_get_fields_errors($rules, $shipping);

    return $errors;
}

function fn_agents_get_company_office_shippings($office_id, $params = array(), $lang = CART_LANGUAGE) {
    $query = db_process('SELECT * FROM ?:company_office_shippings WHERE office_id = ?i ORDER BY shipping_name ASC', array($office_id));
    $shippings = db_get_array($query);
    return $shippings;
}

function fn_agents_process_order_address($order_fields, $params = array(), $lang = CART_LANGUAGE) {
    $office = fn_agents_get_company_offices(null, array('office_id' => $order_fields['office']) );
    $office = $office[0];
    $address = $office['address'];
    $query = db_process(
        'SELECT
            c.CityId as city_id,
            rl.parent_id as region_id,
            cnl.parent_id as country_id,
            cl.name as city,
            rl.name as region,
            cnl.Country as country
        FROM Cities c
        LEFT JOIN Cities_lang cl ON cl.parent_id = c.CityId
        LEFT JOIN Regions_lang rl ON rl.parent_id = c.RegionID
        LEFT JOIN Countries_lang cnl ON cnl.parent_id = c.CountryID
        WHERE c.CityId = ?i
            AND cl.lang_id = ?s
            AND rl.lang_id = ?s
            AND cnl.lang_id = ?s',
        array($office['city_id'], $lang, $lang, $lang) );
    $locations = db_get_array($query);
    $locations = $locations[0];
    $locations['address'] = $address;
    return $locations;
}

function fn_agents_get_city_details($city_id, $lang = CART_LANGUAGE) {
    $query = db_process(
        'SELECT
            c.CityId as city_id,
            rl.parent_id as region_id,
            cnl.parent_id as country_id,
            cl.name as city,
            rl.name as region,
            cnl.Country as country
        FROM Cities c
        LEFT JOIN Cities_lang cl ON cl.parent_id = c.CityId
        LEFT JOIN Regions_lang rl ON rl.parent_id = c.RegionID
        LEFT JOIN Countries_lang cnl ON cnl.parent_id = c.CountryID
        WHERE c.CityId = ?i
            AND cl.lang_id = ?s
            AND rl.lang_id = ?s
            AND cnl.lang_id = ?s',
        array($city_id, $lang, $lang, $lang) );
    $details = db_get_array($query);
    return $details[0];
}

function fn_agents_locations_to_address($locations, $with_address = false) {
    $address = '';
    if($with_address) {
        $address .= $locations['address'] . ', ';
    }
    $address .= $locations['city'] . ', ' . $locations['country'] . ', ' . $locations['region'];
    return $address;
}
function fn_agents_extract_cities_from_offices($offices, $full_info = true) {
    $cities = array();
    if (empty($offices)) {
        return $cities;
    }
    foreach($offices as $office) {
        $cities[$office['city_id']] = array(
            'city_id' => $office['city_id'],
            'city' => $office['city']
        );
    }
    if($full_info) {
        $cities_ids = array_keys($cities);
        $cities = fn_agents_get_all_cities(array('city_id' => $cities_ids), CART_LANGUAGE, true);
    }
    return $cities;
}

function fn_agents_prepare_ajax_options($source_array, $value_key, $text_key, $data_keys=array()) {
    $ajaxResult = array();
    foreach($source_array as $el) {
        $ajaxResult[$el[$value_key]] = array('value' => $el[$value_key], 'text' => $el[$text_key]);
        foreach($data_keys as $ajax_key => $source_key) {
            $ajaxResult[$el[$value_key]]['data'][$ajax_key] = $source_array[$source_key];
        }
    }
    $ajaxResult = array_values($ajaxResult);
    $ajaxResult['length'] = count($ajaxResult);
    return $ajaxResult;
}


function fn_agents_paginate_items($user_id, $count_params, $items, $controller_mode,  $limit=10, $page=1) {
    $count = count($items);
    $total_pages = ceil($count / $limit);
    $pagination = array(
        'page' => $page,
        'total_pages' =>$total_pages,
        'pages' => range(1, $total_pages ),
        'url' => fn_url($controller_mode)
    );
    return $pagination;
}

function fn_agents_paginate_products($user_id, $count_params, $limit=10, $page=1) {
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_products($count_params, $limit, $page );
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.companies_and_products', $limit, $page);
    return $pagination;
}

function fn_agents_paginate_clients($user_id, $count_params, $limit=10, $page=1) {
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_clients($user_id, $count_params);
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.clients', $limit, $page);
    return $pagination;
}

function fn_agents_paginate_collegues($user_id, $count_params, $limit=10, $page=1) {
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_collegues($user_id, $count_params);
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.collegues', $limit, $page);
    return $pagination;
}

function fn_agents_paginate_orders($user_id, $count_params, $limit=10, $page=1) {
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_orders($user_id, $count_params);
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.orders', $limit, $page);
    return $pagination;
}

function fn_agents_paginate_saved_orders($user_id, $count_params, $limit=10, $page=1) {
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_saved_orders($user_id, $count_params);
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.orders_saved', $limit, $page);
    return $pagination;
}

function fn_agents_paginate_active_orders($user_id, $count_params, $limit=10, $page=1) {
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_active_orders($user_id, $count_params);
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.orders_active', $limit, $page);
    return $pagination;
}

function fn_agents_paginate_closed_orders($user_id, $count_params, $limit=10, $page=1) {
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_closed_orders($user_id, $count_params);
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.orders_closed', $limit, $page);
    return $pagination;
}


function fn_agents_prepare_pagination_count_params(&$count_params) {
    unset($count_params['limit']);
    unset($count_params['items_per_page']);
    unset($count_params['page']);
    return $count_params;
}

function fn_agents_get_plan_product_profit($plan, $product) {
    if(empty($plan) || empty($product)) {
        return 0;
    }
    $price = $product['price'];
    $p_id = $product['product_id'];
    $c_id = $product['main_category'];
    $p_ids = is_array($plan['product_ids']) ? array_keys($plan['product_ids']) : array();
    $c_ids = is_array($plan['category_ids']) ? array_keys($plan['category_ids']) : array();
    $profit = 0;
    if (is_array($p_ids) && in_array($p_id, $p_ids) ) {
        $commision = $plan['product_ids'][$p_id];
        if($commision['value_type'] == 'P') {
            $profit = $price * $commision['value'] / 100;
        } elseif ($commision['value_type'] == 'A') {
            $profit = $commision['value'];
        }
    } elseif (is_array($c_ids) && in_array($c_id, $c_ids) ) {
        $commision = $plan['category_ids'][$c_id];
        if($commision['value_type'] == 'P') {
            $profit = $price * $commision['value'] / 100 ;
        } elseif ($commision['value_type'] == 'A') {
            $profit = $commision['value'];
        }
    } elseif (  !empty($plan['commissions'][0]) ) {
        $profit = $price * $plan['commissions'][0] / 100 ;
    }
    return $profit;
}

function fn_agents_sort_products_by_profit($products, $order) {
    $profit_mapped_products = array();
    foreach ($products as $product) {
        $profit_mapped_products[$product['profit']][$product['product_id']] = $product;
    }
    if($order == 'asc') {
        ksort($profit_mapped_products);
    } elseif ($order == 'desc') {
        krsort($profit_mapped_products);
    }
    $sorted_products = array();
    foreach($profit_mapped_products as $product_profit_group) {
        foreach($product_profit_group as $pid => $product) {
            $sorted_products[$pid] = $product;
        }
    }
    return $sorted_products;
}

//function fn_agents_get_company_logos() {
//    $query = db_process('SELECT * FROM ?:companies WHERE 1');
//    $companies = db_get_array($query);
//    $logos = array();
//    foreach($companies as $company) {
//        $logo_data = unserialize($company['logos']);
//        if (empty($logo_data['Customer_logo'])) {
//            continue;
//        }
//        $logo = $logo_data['Customer_logo'];
//        $logo['filename'] = REAL_URL . 'images/' . $logo['filename'];
//        $logos[] = array_merge($logo , array(
//                'company_id' => $company['company_id'],
//                'company' => $company['company']
//            )
//        );
//    }
//    return $logos;
//}

function fn_agents_get_payout_date($payout_id, $format = '%b %e, %Y') {
    if(empty($payout_id)) {
        return '';
    }
    $date = db_get_field(db_process('SELECT date FROM ?:affiliate_payouts WHERE payout_id = ?i', array($payout_id)));
    if(!empty($format)) {
        return fn_date_format($date, $format);
    } else {
        return $date;
    }
}

function fn_agents_get_saved_products($user_id, $params) {
    $saved_orders = db_get_array(db_process('SELECT product_id FROM ?:orders_saved WHERE user_id = ?i', array($user_id) ));
    if (empty($saved_orders)) {
        return array();
    }
    $saved_orders_ids = array();
    foreach($saved_orders as $order) {
        $saved_orders_ids[] = $order['product_id'];
    }

    $params['filter_params'][ 'product_id' ] = $saved_orders_ids;
    $products = fn_agents_get_products($params);
    return $products[0];
}

function fn_agents_paginate_saved_products($user_id, $params, $limit, $page){
    fn_agents_prepare_pagination_count_params($count_params);
    $items = fn_agents_get_saved_products($user_id, $count_params);
    $pagination = fn_agents_paginate_items($user_id, $count_params, $items, 'agents.orders_saved', $limit, $page);
    return $pagination;
}

function fn_agents_add_slide() {
    $slider_types = array('top', 'company', 'products');
    foreach($slider_types as $slider_type) {
        $logo  = fn_filter_uploaded_data($slider_type);
        $logo = $logo[0];
        if(!empty($logo)) {
            $short_name = "sliders/{$slider_type}/{$logo['name']}";
            $filename = DIR_IMAGES . $short_name;
            fn_mkdir(dirname($filename));

            if (fn_get_image_size($logo['path'])) {
                $dot = strrpos($filename, '.');
                $filename_original = substr($filename, 0, $dot) . '_orig' . substr($filename, $dot);
                if (fn_copy($logo['path'], $filename_original)) {
                    if (fn_resize_image($filename_original, $filename, Registry::get('settings.Thumbnails.product_lists_thumbnail_width'), Registry::get('settings.Thumbnails.product_lists_thumbnail_height'), true, Registry::get('settings.Thumbnails.thumbnail_background_color'), true)) {
                    }
                    list($w, $h, ) = fn_get_image_size($filename);
                    list($w_orig, $h_orig) = fn_get_image_size($filename_original);
                    $alt = empty($_REQUEST[$slider_type]['alt']) ? '' : $_REQUEST[$slider_type]['alt'];
                    $name = empty($_REQUEST[$slider_type]['name']) ? '' : $_REQUEST[$slider_type]['name'];
                    fn_trusted_vars($slider_type);
                    $description = empty($_REQUEST[$slider_type]['description']) ? '' : $_REQUEST[$slider_type]['description'];
                    $logoEntity = array(
                        'filename' => $filename,
                        'width' => $w,
                        'height' => $h,
                        'type' => $slider_type,
                        'alt' => $alt,
                        'name' => $name,
                        'description' => $description,
                        'filename_original' => $filename_original,
                        'width_original' => $w_orig,
                        'height_original' => $h_orig
                    );
                    if(!empty($_REQUEST['slide_id'])) {
                        $sid = $_REQUEST['slide_id'];
                        $query = db_process("UPDATE ?:slider_logos SET ?e WHERE slide_id = ?i ", array($logoEntity, $sid));
                        if( db_query($query) ) {
                            fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('text_changes_saved'));
                        } else {
                            fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('file_not_uploaded'));
                        }
                    } else {
                        $query = db_process("INSERT INTO ?:slider_logos ?e ", array($logoEntity));
                        if( db_query($query) ) {
                            fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('text_changes_saved'));
                        } else {
                            fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('file_not_uploaded'));
                        }
                    }
                }
            } else {
                $text = fn_get_lang_var('text_cannot_create_file');
                $text = str_replace('[file]', $filename, $text);
                fn_set_notification('E', fn_get_lang_var('error'), $text);
            }
    }

}




}

/*function fn_agents_get_sliders() {
    $query = db_process('SELECT * FROM ?:slider_logos');
    $db_sliders = db_get_array($query);
    $sliders = array();
    foreach($db_sliders as $slider) {
        $sliders[$slider['type']][] = $slider;
    }
    return $sliders;
}*/

function fn_agents_get_site_order_profit($order, &$company_profit_percents ) {
    if (empty($order['total']) || empty($order['company_id'])) {
        return 0;
    }
    if (empty($company_profit_percents[$order['company_id']])) {
        $query = db_process("SELECT commission FROM ?:companies WHERE company_id = ?i", array($order['company_id']));
        $company_profit_percents[$order['company_id']] = doubleval(db_get_field($query));
    }
    return $order['total'] * (100 - $company_profit_percents[$order['company_id']]) / 100;
}

function fn_agents_get_product_offices($pid) {
    return db_get_array("SELECT * FROM ?:product_offices WHERE product_id = ?i", $pid);
}

function fn_agents_get_product_offices_ids($pid) {
    $pos = fn_agents_get_product_offices($pid);
    $product_offices_ids = array();
    foreach($pos as $po) {
        $product_offices_ids[] = $po['office_id'];
    }
    return $product_offices_ids;
}