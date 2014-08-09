<?php



if (!defined('AREA')) { die('Access denied'); }
const PROFILE_TYPE_CLIENT = 'S';
if (empty($auth['user_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
}

if (!empty($auth['user_type']) && $auth['user_type'] != 'P' || empty($auth['user_type'])) {
    return array(CONTROLLER_STATUS_DENIED);
}

include dirname(__FILE__) . "/fn_agents.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Create/Update user
    //
    if ($mode == 'update') {
        if (fn_image_verification('use_for_register', $_REQUEST) == false) {
            fn_save_post_data('user_data');

            return array(CONTROLLER_STATUS_REDIRECT, 'agents.add');
        }

        $is_update = !empty($_REQUEST['user_data']['user_id']);

        if (!$is_update) {
            $is_valid_user_data = true;

            if (empty($_REQUEST['user_data']['email'])) {
                fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('error_validator_required', array('[field]' => fn_get_lang_var('email'))));
                $is_valid_user_data = false;

            } elseif (!fn_validate_email($_REQUEST['user_data']['email'])) {
                fn_set_notification('W', fn_get_lang_var('error'), fn_get_lang_var('text_not_valid_email', array('[email]' => $_REQUEST['user_data']['email'])));
                $is_valid_user_data = false;
            }
            $_REQUEST['user_data']['password1'] = $_REQUEST['user_data']['password2'] = fn_generate_guest_password();
            if (empty($_REQUEST['user_data']['password1']) || empty($_REQUEST['user_data']['password2'])) {

                if (empty($_REQUEST['user_data']['password1'])) {
                    fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('error_validator_required', array('[field]' => fn_get_lang_var('password'))));
                }

                if (empty($_REQUEST['user_data']['password2'])) {
                    fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('error_validator_required', array('[field]' => fn_get_lang_var('confirm_password'))));
                }
                $is_valid_user_data = false;

            } elseif ($_REQUEST['user_data']['password1'] !== $_REQUEST['user_data']['password2']) {
                fn_set_notification('W', fn_get_lang_var('warning'), fn_get_lang_var('error_validator_password', array('[field2]' => fn_get_lang_var('password'), '[field]' => fn_get_lang_var('confirm_password'))));
                $is_valid_user_data = false;
            }

            if (!$is_valid_user_data) {
                return array(CONTROLLER_STATUS_REDIRECT, 'agents.add');
            }
        }

        fn_restore_processed_user_password($_REQUEST['user_data'], $_POST['user_data']);

        $res = fn_update_subagent($_REQUEST['user_data']['user_id'], $_REQUEST['user_data'], $auth, !empty($_REQUEST['ship_to_another']), true);

        if ($res) {
            list($user_id, $profile_id) = $res;

//             Cleanup user info stored in cart
//            if (!empty($_SESSION['cart']) && !empty($_SESSION['cart']['user_data'])) {
//                unset($_SESSION['cart']['user_data']);
//            }

            // Delete anonymous authentication
//            if ($cu_id = fn_get_session_data('cu_id') && !empty($auth['user_id'])) {
//                fn_delete_session_data('cu_id');
//            }

//            Session::regenerateId();

            if (!empty($_REQUEST['return_url'])) {
                return array(CONTROLLER_STATUS_OK, $_REQUEST['return_url']);
            }

        } else {
            fn_save_post_data('user_data');
            fn_delete_notification('changes_saved');
        }

        if (!empty($user_id) && !$is_update) {
            $redirect_url = "profiles.success_add";
        } else {
            $redirect_url = "profiles." . (!empty($user_id) ? "update" : "add") . "?";

            if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
                $redirect_url .= "profile_id=$profile_id&";
            }

            if (!empty($_REQUEST['return_url'])) {
                $redirect_url .= 'return_url=' . urlencode($_REQUEST['return_url']);
            }
        }

        return array(CONTROLLER_STATUS_OK, $redirect_url);
    }
}

if ($mode == 'add') {

    if (!empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "profiles.update");
    }

    fn_add_breadcrumb(fn_get_lang_var('registration'));

    $user_data = array();
    if (!empty($_SESSION['cart']) && !empty($_SESSION['cart']['user_data'])) {
        $user_data = $_SESSION['cart']['user_data'];
    }

    $restored_user_data = fn_restore_post_data('user_data');
    if ($restored_user_data) {
        $user_data = fn_array_merge($user_data, $restored_user_data);
    }

    Registry::set('navigation.tabs.general', array (
        'title' => fn_get_lang_var('general'),
        'js' => true
    ));

    $params = array();
    if (isset($_REQUEST['user_type'])) {
        $params['user_type'] = $_REQUEST['user_type'];
    }

    $profile_fields = fn_get_profile_fields('C', array(), CART_LANGUAGE, $params);

    Registry::get('view')->assign('profile_fields', $profile_fields);
    Registry::get('view')->assign('user_data', $user_data);
    Registry::get('view')->assign('ship_to_another', fn_check_shipping_billing($user_data, $profile_fields));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());

}
elseif ($mode == 'add_subagent') {
    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=".urlencode(Registry::get('config.current_url')));
    }
    Registry::get('view')->assign('content_tpl', 'views/agents/update_subagent.tpl');

    $profile_id = null; /*empty($_REQUEST['profile_id']) ? 0 : $_REQUEST['profile_id'];*/
    fn_add_breadcrumb(fn_get_lang_var('editing_profile'));

//    if (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new') {
//        $user_data = fn_get_user_info($auth['user_id'], false);
//    } else {
//        $user_data = fn_get_user_info($auth['user_id'], true, $profile_id);
//    }

    $user_data = fn_get_agent_by_id($auth['user_id']);

    if (empty($user_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    $subagent_data = $_REQUEST['user_data'];
    $subagent_data['curator_id'] = $user_data['user_id'];
    $subagent_data['company_id'] = $user_data['company_id'];
    $subagent_data['company'] = $user_data['company'];

    if(empty ($_REQUEST['user_data'])) {
//        Registry::get('view')->display('views/agents/update_subagent.tpl');
        Registry::get('view')->assign('user_data', $subagent_data);
        return array(CONTROLLER_STATUS_OK);
    }
    $restored_user_data = fn_restore_post_data('user_data');
    if ($restored_user_data) {
        $subagent_data = fn_array_merge($subagent_data, $restored_user_data);
    }
    $res = fn_update_subagent(null, $subagent_data, $qwer=array(), !empty($_REQUEST['ship_to_another']), true);

    Registry::set('navigation.tabs.general', array (
        'title' => fn_get_lang_var('general'),
        'js' => true
    ));

    $show_usergroups = true;
    if (Registry::get('settings.General.allow_usergroup_signup') != 'Y') {
        $show_usergroups = fn_user_has_active_usergroups($user_data);
    }

    if ($show_usergroups) {
        $usergroups = fn_get_usergroups('C');
        if (!empty($usergroups)) {
            Registry::set('navigation.tabs.usergroups', array (
                'title' => fn_get_lang_var('usergroups'),
                'js' => true
            ));

            Registry::get('view')->assign('usergroups', $usergroups);
        }
    }

    $profile_fields = array();

    Registry::get('view')->assign('profile_fields', $profile_fields);
    Registry::get('view')->assign('user_data', $subagent_data);
    Registry::get('view')->assign('ship_to_another', fn_check_shipping_billing($subagent_data, $profile_fields));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());
    if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
        Registry::get('view')->assign('user_profiles', fn_get_user_profiles($subagent_data['user_id']));
    }

}
elseif ($mode == 'update') {

    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=".urlencode(Registry::get('config.current_url')));
    }

    $profile_id = empty($_REQUEST['profile_id']) ? 0 : $_REQUEST['profile_id'];
    fn_add_breadcrumb(fn_get_lang_var('editing_profile'));

    if (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new') {
        $user_data = fn_get_user_info($auth['user_id'], false);
    } else {
        $user_data = fn_get_user_info($auth['user_id'], true, $profile_id);
    }

    if (empty($user_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $restored_user_data = fn_restore_post_data('user_data');
    if ($restored_user_data) {
        $user_data = fn_array_merge($user_data, $restored_user_data);
    }

    Registry::set('navigation.tabs.general', array (
        'title' => fn_get_lang_var('general'),
        'js' => true
    ));

    $show_usergroups = true;
    if (Registry::get('settings.General.allow_usergroup_signup') != 'Y') {
        $show_usergroups = fn_user_has_active_usergroups($user_data);
    }

    if ($show_usergroups) {
        $usergroups = fn_get_usergroups('C');
        if (!empty($usergroups)) {
            Registry::set('navigation.tabs.usergroups', array (
                'title' => fn_get_lang_var('usergroups'),
                'js' => true
            ));

            Registry::get('view')->assign('usergroups', $usergroups);
        }
    }

    $profile_fields = fn_get_profile_fields();

    Registry::get('view')->assign('profile_fields', $profile_fields);
    Registry::get('view')->assign('user_data', $subagent_data);
    Registry::get('view')->assign('ship_to_another', fn_check_shipping_billing($subagent_data, $profile_fields));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());
    if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
        Registry::get('view')->assign('user_profiles', fn_get_user_profiles($auth['user_id']));
    }

}
elseif ($mode == 'usergroups') {
    if (empty($auth['user_id']) || empty($_REQUEST['type']) || empty($_REQUEST['usergroup_id'])) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    if (fn_request_usergroup($auth['user_id'], $_REQUEST['usergroup_id'], $_REQUEST['type'])) {
        $user_data = fn_get_user_info($auth['user_id']);
        fn_send_mail(
            Registry::get('settings.Company.company_users_department'),
            Registry::get('settings.Company.company_users_department'),
            'support/new_ticket_subj.tpl',
            'profiles/profile_activated.tpl',
            null,
            CART_LANGUAGE,
            $user_data['email'],
            true,
            $user_data['company_id']
        );

    }

    return array(CONTROLLER_STATUS_OK, "profiles.update");

}
elseif ($mode == 'success_add') {

    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "agents.add");
    }

    fn_add_breadcrumb(fn_get_lang_var('registration'));
}
elseif($mode == 'office') {
    return array(CONTROLLER_STATUS_OK);
}
elseif($mode == 'companies_and_products') {
    if ( empty($_REQUEST['limit']) || !intval(($_REQUEST['limit']) )) {
        $limit = $_REQUEST['limit'] =  10;
    } else {
        $limit = $_REQUEST['limit'];
    }
    $page = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];

    $all_products = fn_agent_get_products(array('client' => array('company'=>$_REQUEST['client']['company']) ), null, CART_LANGUAGE, null, false );
    $products = fn_agent_get_products($_REQUEST, $limit );
    $count_params = $_REQUEST;
    unset($count_params['limit']);
    unset($count_params['items_per_page']);
    unset($count_params['page']);
    $products_count = fn_agent_get_products($count_params);
    $products_count = count($products_count[0]);
    foreach($products[0] as &$product) {
        $product['image']['image_path'] = get_image_full_path($product['image']);
        $product['company'] = fn_agents_get_company_info($product['company_id']);
        $product['company']['image_path'] = get_image_full_path($product['company']);
        $product['description'] = fn_agents_get_product_description($product['product_id']);
    }
    unset($product);
    $total_pages = ceil($products_count / $limit);
    $pagination = array(
        'page' => $page,
        'total_pages' =>$total_pages,
        'pages' => range(1, $total_pages ),
        'url' => fn_url('agents.companies_and_products')
    );

    $companies = fn_get_companies(null, $auth);
    Registry::get('view')->assign('all_products', $all_products[0]);
    Registry::get('view')->assign('products', $products[0]);
    Registry::get('view')->assign('products_param', $products[1]);
    Registry::get('view')->assign('companies', $companies[0]);
    Registry::get('view')->assign('mode', 'products');
    Registry::get('view')->assign('pagination', $pagination);
    Registry::get('view')->assign('client', $_REQUEST['client']);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');


    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'order_make') {

    $step = empty($_REQUEST['step']) ? 1 : intval($_REQUEST['step'])+1 ;
    if(empty($_REQUEST['client']['notify'])) {
        $email_fields = array();
    } else {
        $email_fields = array('email');
    }
    $errors = fn_agents_get_client_fields_errors($_REQUEST['client'], array('email_fields' => $email_fields) );

    if ($step == 2) {
        if(!empty($errors)){
            fn_agents_display_errors($errors);
            $step = 1;
        }
        else{
            fn_agents_process_order($_REQUEST, $step, $auth);
        }
    }
    if ($step == 3) {
        fn_agents_process_order($_REQUEST, $step, $auth);

    }
    Registry::get('view')->assign('step', $step );
    Registry::get('view')->assign('product', array(
        'product_id' => $_REQUEST['product_id'],
        'amount' => ($_REQUEST['item_count'] || 1) )
    );
    Registry::get('view')->assign('mode', 'order_make');
    Registry::get('view')->assign('client', empty($_REQUEST['client']) ? array() : $_REQUEST['client']);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'order_save') {
    if (empty($_SESSION['cart'])) {
        fn_clear_cart($_SESSION['cart']);
    }

    $cart = & $_SESSION['cart'];
    fn_clear_cart($cart);
    fn_agents_add_affiliate_data_to_cart($cart, $auth);

//    $cart['affiliate']['code'] = empty($auth['user_id']) ? '' : fn_dec2any($auth['user_id']);
//    $_partner_id = $auth['user_id'];
//    $_partner_data = db_get_row("SELECT firstname, lastname, user_id as partner_id FROM ?:users WHERE user_id = ?i AND user_type = 'P'", $_partner_id);
//    if (!empty($_partner_data)) {
//        $cart['affiliate'] = $_partner_data + $cart['affiliate'];
//        $_SESSION['partner_data'] = array(
//            'partner_id' => $cart['affiliate']['partner_id'],
//            'is_payouts' => 'N'
//        );
//    } else {
//        unset($cart['affiliate']['partner_id']);
//        unset($cart['affiliate']['firstname']);
//        unset($cart['affiliate']['lastname']);
//        unset($_SESSION['partner_data']);
//    }

    $product_data = array($_REQUEST['product_id'] => array(
        'product_id' => $_REQUEST['product_id'],
        'amount' => (empty($_REQUEST['item_count']) ? 1 : $_REQUEST['item_count']  )
    ));

    fn_add_product_to_cart($product_data, $cart, $auth);
    fn_save_cart_content($cart, $auth['user_id']);
    fn_calculate_cart_content($cart, $auth, 'S', true, 'F', true);
    fn_agents_save_order($cart, $auth);
    fn_clear_cart($cart);
    fn_save_cart_content($cart, $auth['user_id']);


    Registry::get('view')->assign('product', $product );
    Registry::get('view')->assign('mode', 'order_save');
    Registry::get('view')->assign('client', empty($_REQUEST['client']) ? array() : $_REQUEST['client']);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'clients') {
    $clients = fn_agents_get_clients($auth['user_id'], $_REQUEST);
    Registry::get('view')->assign('mode', 'clients');
    Registry::get('view')->assign('clients', $clients );
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'add_client_form') {
    $client_data = array('affiliate_id' => $auth['user_id']);
    if($_REQUEST['submit'] == fn_get_lang_var('submit')){
        $client_data = array(
            'affiliate_id' => $auth['user_id'],
            'fio' => $_REQUEST['profile_name'],
            'phone' => $_REQUEST['b_phone'],
            'email' => $_REQUEST['b_email'],
            'comment' =>  $_REQUEST['comment'],
        );
        $errors = fn_agents_get_client_fields_errors($client_data);
        if (empty($errors) ) {
            $client_id = fn_agents_register_customer($client_data);
        } else {
           fn_agents_display_errors($errors);
        }
        if(!empty($client_id)) {
            fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('agents_new_client_registered'));
        }
    }


    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    Registry::get('view')->assign('mode', 'add_client_form');
    Registry::get('view')->assign('client', $_REQUEST );
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'collegues') {
    $collegues = fn_agents_get_collegues($auth['user_id']);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    Registry::get('view')->assign('mode', 'collegues');
    Registry::get('view')->assign('collegues', $collegues);
    Registry::get('view')->assign('client', $_REQUEST );
}
elseif ($mode == 'orders') {
    $products = fn_agent_get_products(array('company_id'=>$_REQUEST['where']['company_id'] ), null, CART_LANGUAGE, null, false );
    $companies = fn_get_companies(null, $auth);
    $orders = fn_agent_get_orders($auth['user_id'], $_REQUEST);

    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    Registry::get('view')->assign('order_statuses', fn_agents_get_order_statuses());
    Registry::get('view')->assign('mode', 'orders');
    Registry::get('view')->assign('products', $products[0]);
    Registry::get('view')->assign('companies', $companies[0]);
    Registry::get('view')->assign('where', $_REQUEST['where'] );
    Registry::get('view')->assign('order', $_REQUEST['order'] );
    Registry::get('view')->assign('orders', $orders );
    return array(CONTROLLER_STATUS_OK);
}
//elseif ($mode == 'company_info') {
//    $active_tab = empty($_REQUEST['active_tab']) ? 'company' : $_REQUEST['active_tab'];
//    Registry::get('view')->assign('active_tab', $active_tab);
//
//    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
//    Registry::get('view')->assign('order_statuses', fn_agents_get_order_statuses());
//    Registry::get('view')->assign('mode', 'company_info');
//    Registry::get('view')->assign('where', $_REQUEST['where'] );
//    Registry::get('view')->assign('order', $_REQUEST['order'] );
//    Registry::get('view')->assign('cities', $cities);
//    Registry::get('view')->assign('city', $city);
//    Registry::get('view')->assign('company', $company );
//    Registry::get('view')->assign('office', $office );
//    Registry::get('view')->assign('offices', $offices );
//    Registry::get('view')->assign('shipping_descriptions', $shipping_descriptions );
//    return array(CONTROLLER_STATUS_OK);
//}
elseif ($mode == 'orders_saved') {
    $products = fn_agent_get_products(array('client' => array('company'=>$_REQUEST['where']['company_id']) ), null, CART_LANGUAGE, null, false );
    $companies = fn_get_companies(null, $auth);
    $orders = fn_agent_get_saved_orders($auth['user_id'], $_REQUEST);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    Registry::get('view')->assign('order_statuses', fn_agents_get_order_statuses());
    Registry::get('view')->assign('mode', 'orders');
    Registry::get('view')->assign('products', $products[0]);
    Registry::get('view')->assign('companies', $companies[0]);
    Registry::get('view')->assign('where', $_REQUEST['where'] );
    Registry::get('view')->assign('order', $_REQUEST['order'] );
    Registry::get('view')->assign('orders', $orders );
    return array(CONTROLLER_STATUS_OK);
}

elseif ($mode == 'orders_active') {
    $products = fn_agent_get_products(array('client' => array('company'=>$_REQUEST['where']['company_id']) ), null, CART_LANGUAGE, null, false );
    $companies = fn_get_companies(null, $auth);
    $orders = fn_agent_get_active_orders($auth['user_id'], $_REQUEST);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    Registry::get('view')->assign('order_statuses', fn_agents_get_order_statuses());
    Registry::get('view')->assign('mode', 'orders');
    Registry::get('view')->assign('products', $products[0]);
    Registry::get('view')->assign('companies', $companies[0]);
    Registry::get('view')->assign('where', $_REQUEST['where'] );
    Registry::get('view')->assign('order', $_REQUEST['order'] );
    Registry::get('view')->assign('orders', $orders );
    return array(CONTROLLER_STATUS_OK);
}

elseif ($mode == 'orders_closed') {
    $products = fn_agent_get_products(array('client' => array('company'=>$_REQUEST['where']['company_id']) ), null, CART_LANGUAGE, null, false );
    $companies = fn_get_companies(null, $auth);
    $orders = fn_agent_get_closed_orders($auth['user_id'], $_REQUEST);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    Registry::get('view')->assign('order_statuses', fn_agents_get_order_statuses());
    Registry::get('view')->assign('mode', 'orders');
    Registry::get('view')->assign('products', $products[0]);
    Registry::get('view')->assign('companies', $companies[0]);
    Registry::get('view')->assign('where', $_REQUEST['where'] );
    Registry::get('view')->assign('order', $_REQUEST['order'] );
    Registry::get('view')->assign('orders', $orders );
    return array(CONTROLLER_STATUS_OK);
}
elseif($mode == 'product_info' || $mode == 'company_info') {
    if (empty ($_REQUEST['product_id'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    $product = fn_agent_get_products(array('product_id' => $_REQUEST['product_id'], 'pid' => $_REQUEST['product_id']) );
    $product = $product[0][0];
    if(empty ($product)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $product['image']['image_path'] = get_image_full_path($product['image']);
    $product['description'] = fn_agents_get_product_description($_REQUEST['product_id']);
    $company = fn_agents_get_company_info($product['company_id']);
    $company['image_path'] = get_image_full_path($company);
    Registry::get('view')->assign('content_tpl', 'views/agents/office.tpl');
    Registry::get('view')->assign('order_statuses', fn_agents_get_order_statuses());
    Registry::get('view')->assign('mode', 'product_info');
    Registry::get('view')->assign('product', $product);
    Registry::get('view')->assign('company', $company);


    if($mode == 'product_info') {
        $active_tab = 'product';
    } else {
        $active_tab = 'company';
    }
    $active_tab = empty($_REQUEST['active_tab']) ? $active_tab : $_REQUEST['active_tab'];
    Registry::get('view')->assign('active_tab', $active_tab);

    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'update_client') {
    if(empty($_REQUEST["profile_id"]) || empty($_REQUEST["field"]) || empty($_REQUEST["value"]) ) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    $field = $_REQUEST['field'];
    $profile_id = $_REQUEST["profile_id"];
    $value = $_REQUEST["value"];
    $res = db_query(db_process("UPDATE ?:user_profiles SET $field = ?s WHERE profile_id = ?i", array($value, $profile_id)  ));
    if($res) {
        echo(json_encode(array('status'=>'OK')));
       die();
    }
    return array(CONTROLLER_STATUS_NO_PAGE);
}

