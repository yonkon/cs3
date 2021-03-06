<?php



if (!defined('AREA')) { die('Access denied'); }
const PROFILE_TYPE_CLIENT = 'S';
if (empty($auth['user_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
}

if (!empty($auth['user_type']) && $auth['user_type'] != 'P' || empty($auth['user_type'])) {
    return array(CONTROLLER_STATUS_DENIED);
}

require_once(dirname(__FILE__) . "/fn_agents.php");

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

            if (!empty($_REQUEST['return_url'])) {
                return array(CONTROLLER_STATUS_OK, $_REQUEST['return_url']);
            }

        } else {
            fn_save_post_data('user_data');
            fn_delete_notification('changes_saved');
        }

        if (!empty($user_id) && !$is_update) {
            $redirect_url = "agents.office";
        } else {
            $redirect_url = "agents." . (!empty($user_id) ? "update" : "add") . "?";

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

$view = Registry::get('view');
if (!in_array($mode, array(
    'add', 'add_subagent', 'update', 'usergroups', 'success_add', 'office',
    'update_client'
    ) ) || strpos($mode, 'ajax') === 0 ) {
    $view->assign('content_tpl', 'views/agents/office.tpl');
    $view->assign('mode', $mode);

}
//if (strpos($mode, 'orders_') === 0 && $mode != 'orders_saved') {
//    $view->assign('mode', 'orders');
//}

$limit = $_REQUEST['limit'] = empty($_REQUEST['limit']) ? 10 : $_REQUEST['limit'];
$page = $_REQUEST['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];

if ($mode == 'add') {
    if (!empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "agents.update");
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

    $view->assign('profile_fields', $profile_fields);
    $view->assign('user_data', $user_data);
    $view->assign('ship_to_another', fn_check_shipping_billing($user_data, $profile_fields));
    $view->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    $view->assign('states', fn_get_all_states());

}
elseif ($mode == 'add_subagent') {
    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=".urlencode(Registry::get('config.current_url')));
    }
    $view->assign('content_tpl', 'views/agents/update_subagent.tpl');

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
//        $view->display('views/agents/update_subagent.tpl');
        $view->assign('user_data', $subagent_data);
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

            $view->assign('usergroups', $usergroups);
        }
    }

    $profile_fields = array();

    $view->assign('profile_fields', $profile_fields);
    $view->assign('user_data', $subagent_data);
    $view->assign('ship_to_another', fn_check_shipping_billing($subagent_data, $profile_fields));
    $view->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    $view->assign('states', fn_get_all_states());
    if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
        $view->assign('user_profiles', fn_get_user_profiles($subagent_data['user_id']));
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

            $view->assign('usergroups', $usergroups);
        }
    }

    $profile_fields = fn_get_profile_fields();

    $view->assign('profile_fields', $profile_fields);
    $view->assign('user_data', $subagent_data);
    $view->assign('ship_to_another', fn_check_shipping_billing($subagent_data, $profile_fields));
    $view->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    $view->assign('states', fn_get_all_states());
    if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
        $view->assign('user_profiles', fn_get_user_profiles($auth['user_id']));
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
elseif ($mode == 'office') {
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'companies_and_products' || $mode == 'new_products') {
    if($mode == 'new_products') {
        $_REQUEST['new'] = 1;
        $view->assign('mode', 'new_products');
    } else {
        $view->assign('mode', 'products');
    }

    $offices = fn_agents_get_company_offices($_REQUEST['client']['company']);
    $cities = fn_agents_extract_cities_from_offices($offices);

    $all_products = fn_agents_get_products(array('client' => array('company'=>$_REQUEST['client']['company']) ), null, CART_LANGUAGE, false );

    if(!empty($_REQUEST['sorting_profit'])) {
        $products = fn_agents_get_products($_REQUEST,null, CART_LANGUAGE, true);
    } else {
        $products = fn_agents_get_products($_REQUEST,null, CART_LANGUAGE, true);
    }

    $affiliate_plan = fn_get_affiliate_plan_data_by_partner_id($auth['user_id']);
    foreach($products[0] as &$product) {
        $product['image']['image_path'] = get_image_full_path($product['image']);
        $product['company'] = fn_agents_get_company_info($product['company_id']);
        $product['company']['image_path'] = fn_agents_get_company_logos($product['company_id'])[0]['filename'];
        $product['description'] = fn_agents_get_product_description($product['product_id']);
        $product['profit'] = fn_agents_get_plan_product_profit($affiliate_plan, $product);
    }
    unset($product);
    $products_param = $products[1];
    $products = $products[0];
    if(!empty($_REQUEST['sort_profit'])) {

        $products = fn_agents_sort_products_by_profit($products, $_REQUEST['sort_profit']);
        if(!empty($limit) && !empty($page)) {
            $i = 0;
            $from = $limit * ($page - 1);
            $to = $from + $limit;
            foreach($products as $pid => $product) {
                if($i < $from || $i >= $to) {
                    unset ($products[$pid]);
                }
            }
        }
    }

    $pagination = fn_agents_paginate_products($auth['user_id'], $_REQUEST, $limit, $page);
    $companies = fn_get_companies(null, $auth);
    $view->assign('all_cities', $cities);
    $view->assign('all_products', $all_products[0]);
    $view->assign('products', $products);
    $view->assign('products_param', $products_param);
    $view->assign('companies', $companies[0]);

    $view->assign('pagination', $pagination);
    $view->assign('client', $_REQUEST['client']);
    $view->assign('request', $_REQUEST);

    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'clients') {
    if ( empty($_REQUEST['limit']) || !intval(($_REQUEST['limit']) )) {
        $limit = $_REQUEST['limit'] =  10;
    } else {
        $limit = $_REQUEST['limit'];
    }
    $page = $_REQUEST['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
    $pagination = fn_agents_paginate_clients($auth['user_id'], $_REQUEST, $limit, $page);

    $clients = fn_agents_get_clients($auth['user_id'], $_REQUEST);
    $view->assign('clients', $clients );
    $view->assign('pagination', $pagination );
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


    $view->assign('client', $_REQUEST );
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'collegues' || $mode == 'collegues_export') {
    if ( empty($_REQUEST['limit']) || !intval(($_REQUEST['limit']) )) {
        $limit = $_REQUEST['limit'] =  10;
    } else {
        $limit = $_REQUEST['limit'];
    }
    $page = $_REQUEST['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
    $pagination = fn_agents_paginate_collegues($auth['user_id'], $_REQUEST, $limit, $page);
    $collegues = fn_agents_get_collegues($auth['user_id'], $_REQUEST);
    $collegues_ids = array();
    $collegues_map = array();
    $count_collegues = count($collegues);
    for ($i = 0; $i<$count_collegues; $i++) {
        $collegue = $collegues[$i];
        $collegues_ids[] = $collegue['user_id'];
        $collegues_map[$collegue['user_id']] = $i;
    }
    $view->assign('collegues', $collegues);
    $view->assign('pagination', $pagination);
    $view->assign('client', $_REQUEST );

    $payout_types = Registry::get('payout_types');
    $view->assign('payout_types', $payout_types);
    $payout_options = array();
    foreach ($payout_types as $payout_id => $payout_data) {
        $payout_options[$payout_id] = fn_get_lang_var($payout_data['title']);
    }
    $view->assign('payout_options', $payout_options);


    $_SESSION['statistic_conditions'] = empty($_SESSION['statistic_conditions']) ? array() : $_SESSION['statistic_conditions'];
    $statistic_conditions = & $_SESSION['statistic_conditions'];

    $_SESSION['statistic_search_data'] = empty($_SESSION['statistic_search_data']) ? array() : $_SESSION['statistic_search_data'];
    $statistic_search_data = & $_SESSION['statistic_search_data'];

    if ($action == 'reset_search' || empty($statistic_conditions)) {
        $statistic_conditions = " (actions.amount != 0) ";
        $statistic_search_data = array();
    }

    $statistic_conditions = 'action="sale" ';
    if (empty($_REQUEST['statistic_search'])) {
        $statistic_search = array();
    } else {
        $statistic_search = $_REQUEST['statistic_search'];
    }

    $statistic_search_data = (empty($search_type) || $search_type != 'add') ? $statistic_search : fn_array_merge($statistic_search_data, $statistic_search);

    $statistic_conditions .= db_quote(" AND (actions.partner_id = ?i AND actions.customer_id IN (?n))", $auth['user_id'], $collegues_ids);
    $statistic_conditions .= " AND actions.partner_id != actions.customer_id ";

    if (!empty($_REQUEST['period']) && $_REQUEST['period'] != 'A') {
        list($_REQUEST['time_from'], $_REQUEST['time_to']) = fn_create_periods($_REQUEST);
        $statistic_search_data['period'] = $_REQUEST['period'];
        if ($_REQUEST['period'] == 'C') {
            $statistic_search_data['start_date'] = $_REQUEST['time_from'];
            $statistic_search_data['end_date'] = $_REQUEST['time_to'];
        }

        $statistic_conditions .= db_quote(" AND (date >= ?i AND date <= ?i)", $_REQUEST['time_from'], $_REQUEST['time_to']); // FIXME
    } else {
        $statistic_search_data['period'] = 'A';
    }

    if (!empty($statistic_search_data['plan_id'])) {
        $statistic_conditions .= db_quote(" AND (actions.plan_id = ?i) ", $statistic_search_data['plan_id']);
    }

    $statistic_conditions .= ''; //" AND actions.payout_id != 0 ";

    $statistic_search_data['amount_from'] = empty($statistic_search_data['amount_from']) ? 0 : floatval($statistic_search_data['amount_from']);
    if (!empty($statistic_search_data['amount_from'])) {
        $statistic_conditions .= db_quote(" AND (actions.amount >= ?d) ", fn_convert_price($statistic_search_data['amount_from']));
    }
    $statistic_search_data['amount_to'] = empty($statistic_search_data['amount_to']) ? 0 : floatval($statistic_search_data['amount_to']);
    if (!empty($statistic_search_data['amount_to'])) {
        $statistic_conditions .= db_quote(" AND (actions.amount <= ?d) ", fn_convert_price($statistic_search_data['amount_to']));
    }

    $view->assign('statistic_search', $statistic_search_data);

    $joins = array();
    $order_status_join = '';
    $payout_join = '';
    $product_join = '';
    $std_payout_join = '';
    if (!empty($_REQUEST['order_status'])) {
        $order_status = $_REQUEST['order_status'];
        $joins[] = $order_status_join = db_process (' JOIN ?:aff_action_links al ON al.action_id = actions.action_id AND al.object_type = "O" JOIN ?:orders o ON o.order_id = al.object_data AND o.status = ?s', array($order_status));
    }
    if (!empty($_REQUEST['paid_date_from']) || !empty($_REQUEST['paid_date_to']) ) {
        $payout_join = db_process(' JOIN ?:affiliate_payouts ap ON ap.payout_id = actions.payout_id ');
        if (!empty($_REQUEST['paid_date_from']) ) {
            $payout_join .= db_process(' AND ap.date >= ?i ', array(strtotime($_REQUEST['paid_date_from'] ) ) );
        }
        if (!empty($_REQUEST['paid_date_to']) ) {
            $payout_join .= db_process(' AND ap.date <= ?i ', array(strtotime($_REQUEST['paid_date_to'] ) ) );
        }
        $joins[] = $payout_join;
    }

    if (!empty ($_REQUEST['product_id']) || !empty ($_REQUEST['company_id'])) {
        if (!empty($order_status_join)) {
            $product_join = db_process('JOIN ?:order_details od ON od.order_id = o.order_id ');
        } else {
            $product_join = db_process(' JOIN ?:aff_action_links al ON al.action_id = actions.action_id AND al.object_type = "O" JOIN ?:orders o ON o.order_id = al.object_data JOIN ?:order_details od ON od.order_id = o.order_id ');
        }
        if (!empty ($_REQUEST['product_id']))  {
            $product_join .= db_process(' AND od.product_id = ?i ', array($_REQUEST['product_id']));
        }

        if (!empty ($_REQUEST['company_id']))  {
            $product_join .= db_process(' JOIN ?:products p ON p.product_id=od.product_id AND p.company_id = ?i ', array($_REQUEST['company_id']));
        }

        $joins[] = $product_join;
    }
    if (!empty ($_REQUEST['customer_id']) ) {
        $statistic_conditions .= db_process(' AND actions.customer_id = ?i ', array($_REQUEST['customer_id']));
    }

    if ( empty($payout_join)) {
        $joins[] = $std_payout_join = db_process(' LEFT JOIN ?:affiliate_payouts ap ON ap.payout_id = actions.payout_id ');
    }

//    $general_stats = db_get_hash_array("SELECT action, actions.customer_id as partner_id, COUNT(action) as count, SUM(actions.amount) as sum, AVG(actions.amount) as avg, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions GROUP BY actions.customer_id", 'partner_id', $order_status_join, $payout_join, $product_join, $std_payout_join);
    $general_stats = db_get_hash_array("SELECT action, actions.customer_id as partner_id, COUNT(action) as count, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions GROUP BY actions.customer_id", 'partner_id', $order_status_join, $payout_join, $product_join, $std_payout_join);


    $general_stats['total'] = db_get_row("SELECT 'Всего' as action, COUNT(action) as count, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions as actions WHERE $statistic_conditions");




    $sort_order = empty($_REQUEST['sort_order']) ? 'desc' : $_REQUEST['sort_order'];
    $sort_by = empty($_REQUEST['sort_by']) ? 'date' : $_REQUEST['sort_by'];

    $list_stats = fn_get_affiliate_actions(array(
        'prepared' => $_SESSION['statistic_conditions'],
        'order_status' => $_REQUEST['order_status'],
        'join' => $joins
    ), array('sort_order' => $sort_order, 'sort_by' => $sort_by), false, @$_REQUEST['page']);


    $view->assign('order_status', $_REQUEST['order_status']);
    $view->assign('sort_order', $sort_order == 'asc' ? 'desc' : 'asc');
    $view->assign('sort_by', $sort_by);

    foreach($list_stats as &$sale) {
        $sale_order  = fn_agents_get_orders(null, array('where' => array('order_id' => $sale['data']['O'])));
        $sale['order'] = $sale_order[0];
        $sale['payout_date'] = fn_agents_get_payout_date($sale['payout_id'], false);
        if($sale['order']['status'] == 'C') {
            $general_stats[$sale['customer_id']]['sum'] += $sale['amount'];
            $general_stats['total']['sum'] += $sale['amount'];
        } else {
            $sale['amount'] = 0;
        }
    }
    unset($sale);
    foreach($general_stats as &$g_st) {
        $_collegue = $collegues[$collegues_map[$g_st['partner_id']]];
        $g_st['action'] = $_collegue['lastname'] . ' ' . $_collegue['firstname'];
    }
    unset($g_st);




    if (!empty($_REQUEST['post_sort_by']) ) {
        $psort = $_REQUEST['post_sort_by'];
        $sorted_list_stats = array();

        if (true || $psort == 'order') {
            $order_ids = array();
            $index = 0;
            foreach($list_stats as $sale) {
                if ($psort == 'order') {
                    $order_ids[$sale['order']['order_id']][] = $index;
                } elseif ($psort == 'company') {
                    $order_ids[
                    preg_replace(
                        '/[^\w^\d]/u', '_',
                        $sale['order']['company_data']['company']
                    )][] = $index;
                } elseif ($psort == 'product') {
                    $order_ids[
                    preg_replace(
                        '/[^\w^\d]/u', '_',
                        $sale['order']['product_data']['product']
                    )][] = $index;
                } elseif ($psort == 'agent' || $psort == 'subagent') {
                    if($sale['partner_id'] == $sale['customer_id']) {
                        $order_ids['agent'][
                        preg_replace(
                            '/[^\w^\d]/u', '_',
                            $sale['customer_lastname']
                        )][] = $index;
                    } else {
                        $order_ids['subagent'][
                        preg_replace(
                            '/[^\w^\d]/u', '_',
                            $sale['customer_lastname']
                        )][] = $index;
                    }
                } elseif ($psort == 'sum') {
                    $order_ids[$sale['order']['total']][] = $index;
                } elseif ($psort == 'status') {
                    $order_ids[
                    preg_replace(
                        '/[^\w^\d]/u', '_',
                        $sale['order']['status_description']
                    )][] = $index;
                } elseif ($psort == 'agent_profit' || $psort == 'subagent_profit') {
                    if($sale['partner_id'] == $sale['customer_id']) {
                        $order_ids['agent'][$sale['amount']][] = $index;
                    } else {
                        $order_ids['subagent'][$sale['amount']][] = $index;
                    }
                } elseif ($psort == 'paid_date') {
                    $order_ids[$sale['payout_date']][] = $index;
                }
                $index++;
            }

            $order_ids = is_array($order_ids) ? $order_ids : array();
            $order_ids['agent'] = is_array($order_ids['agent']) ? $order_ids['agent'] : array();
            $order_ids['subagent'] = is_array($order_ids['subagent']) ? $order_ids['subagent'] : array();

            if ($_REQUEST['sort_order'] == 'desc') {
                if ($psort == 'agent' || $psort == 'agent_profit') {
                    krsort($order_ids['agent']);
                    krsort($order_ids['subagent']);
                    $order_ids = array_merge(
                        $order_ids['agent'],
                        $order_ids['subagent']
                    );
                } elseif ($psort == 'subagent' || $psort == 'subagent_profit') {
                    krsort($order_ids['agent']);
                    krsort($order_ids['subagent']);
                    $order_ids = array_merge(
                        $order_ids['subagent'],
                        $order_ids['agent']
                    );
                } else {
                    krsort($order_ids);
                }
            } else {
                if ($psort == 'agent' || $psort == 'agent_profit') {
                    ksort($order_ids['agent']);
                    ksort($order_ids['subagent']);
                    $order_ids = array_merge(
                        $order_ids['agent'],
                        $order_ids['subagent']
                    );
                } elseif ($psort == 'subagent' || $psort == 'subagent_profit') {
                    ksort($order_ids['agent']);
                    ksort($order_ids['subagent']);
                    $order_ids = array_merge(
                        $order_ids['subagent'],
                        $order_ids['agent']
                    );
                } else {
                    ksort($order_ids);
                }
            }
            foreach($order_ids as $ords) {
                foreach($ords as $index) {
                    $sorted_list_stats[] = array_values($list_stats)[$index];
                }
            }
            $list_stats = $sorted_list_stats;
        }
    }

    if (!empty($list_stats)) {
        $list_stats_count = count($list_stats);
        if (!empty($_REQUEST['page']) ) {
            $view->assign('list_stats', array_slice($list_stats, (intval($_REQUEST['page']) - 1) * $limit, $limit, true) );
            fn_paginate($_REQUEST['page'], $list_stats_count);
        } else {
            $view->assign('list_stats', array_slice($list_stats, 0, $limit, true) );
            fn_paginate(1, $list_stats_count);
        }
    }

    $order_status_descr = fn_get_statuses(STATUSES_ORDER, true, true, true);
    $view->assign('order_status_descr', $order_status_descr);
    $view->assign('order_statuses', fn_agents_get_order_statuses());
    $view->assign('products', fn_agents_get_products(array('company_id' => $_REQUEST['company_id']))[0]);
    $companies = fn_get_companies(null, $auth);
    $view->assign('companies', $companies[0]);
    $view->assign('company_id', $_REQUEST['company_id']);
    $view->assign('product_id', $_REQUEST['product_id']);
    $view->assign('general_stats', $general_stats);



    if ($mode == 'collegues_export') {
// Подключаем класс для работы с excel
        require_once(DIR_LIB . 'phpexcel/Classes/PHPExcel.php');
// Подключаем класс для вывода данных в формате excel
        require_once(DIR_LIB . 'phpexcel/Classes/PHPExcel/Writer/Excel5.php');

// Создаем объект класса PHPExcel
        $xls = new PHPExcel();
// Устанавливаем индекс активного листа
        $xls->setActiveSheetIndex(0);
// Получаем активный лист
        $sheet = $xls->getActiveSheet();

        $user = fn_get_user_info($auth['user_id'], false);
// Подписываем лист
        $report_title = fn_get_lang_var('report_for_agent') . " #" . $user['user_id'] . ' ' . $user['lastname'] . ' '. $user['firstname'] . ' ';
        $sheet->setTitle(mb_substr($report_title,0, 31));
// Вставляем текст в ячейку A1
        $sheet->setCellValue("A1", $report_title);
        $sheet->getStyle('A1')->getFill()->setFillType(
            PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');

        $is_full_report = false;
// Объединяем ячейки
        $sheet->mergeCells('A1:H1');

// Выравнивание текста

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//Report date
        $sheet->setCellValue("A2", fn_get_lang_var("Date"));
        $sheet->setCellValue("B2", fn_date_format(time()));
//General statistics
        $sheet->setCellValue("A4", fn_get_lang_var("general_statistics"));
        $sheet->mergeCells('A4:D4');
        $col = 0;
        $row = 5;
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("profit_source"));
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("orders_count"));
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("agent_total_profit"));
//        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("agent_average_profit"));
        $row++;
        $col = 0;
        foreach($general_stats as $profit_source => $g_st) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['action']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['count']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['sum']);
            $row++;
            $col = 0;
        }
        $row++;
//Sales list
        $sheet->setCellValue("A$row", fn_get_lang_var("details"));
        $sheet->mergeCells("A$row:K$row");
        $row++;

        $col = 0;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("Order")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("company")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("product")); $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("subagent")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("price")); $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("agent_profit_from_subagent")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("status")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("registration_date")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("paid_date"));
        $row++;

        $col = 0;
        foreach($list_stats as $sale) {
            $order = fn_agents_get_orders(null, array('where' => array('order_id' => $sale['data']['O'])));
            $order = $order[0];
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['order_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['company_data']['company']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['product_data']['product']);


            $sheet->setCellValueByColumnAndRow($col++, $row,  fn_get_user_name($sale['customer_id']));

            $sheet->setCellValueByColumnAndRow($col++, $row, $order['total']);

            $sheet->setCellValueByColumnAndRow($col++, $row, $sale['amount']);

            $sheet->setCellValueByColumnAndRow($col++, $row, $sale['order']['status_description']);
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_date_format($sale['date']));
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_agents_get_payout_date($sale['payout_id']));
            $row++;
            $col = 0;
        }

        foreach(range('A','N') as $columnID) {
            $xls->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        $xls->getActiveSheet()->getStyle('A1:' . 'N' . $row)->getAlignment()->setIndent(0);

        $xls->getActiveSheet()->getStyle('A1:' . 'N' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(TRUE);

        $report_name = date('m_d_', time()) . 'report_user_' . $user['user_id'] . '.xls';

        // Выводим HTTP-заголовки
        header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ( "Content-type: application/vnd.ms-excel" );
        header ( "Content-Disposition: attachment; filename=" . $report_name );

        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
// Выводим содержимое файла
        try {
            $objWriter = new PHPExcel_Writer_Excel5($xls);
        } catch (Exception $e) {
            var_dump($e);
            die();
        }
        if(!is_dir(DIR_CUSTOM_FILES . 'reports/')) {
            mkdir(DIR_CUSTOM_FILES . 'reports/');
            chmod(DIR_CUSTOM_FILES . 'reports/', DEFAULT_DIR_PERMISSIONS);
        }
        try {
            $objWriter->save('php://output');
        } catch (Exception $e) {
            var_dump($e);
            die();
        }
        return array(CONTROLLER_STATUS_OK);
    }


}

elseif ($mode == 'report' || $mode == 'report_export') {
    $collegues = fn_agents_get_collegues($auth['user_id'], $_REQUEST);
    $view->assign('collegues', $collegues);
        $payout_types = Registry::get('payout_types');
        $view->assign('payout_types', $payout_types);
        $payout_options = array();
        foreach ($payout_types as $payout_id => $payout_data) {
            $payout_options[$payout_id] = fn_get_lang_var($payout_data['title']);
        }
        $view->assign('payout_options', $payout_options);

        $status_options = array(
            'A' => fn_get_lang_var('approved'),
            'N' => fn_get_lang_var('awaiting_approval'),
            'P' => fn_get_lang_var('paidup'),
        );
        $view->assign('status_options', $status_options);

        $_SESSION['statistic_conditions'] = empty($_SESSION['statistic_conditions']) ? array() : $_SESSION['statistic_conditions'];
        $statistic_conditions = & $_SESSION['statistic_conditions'];

        $_SESSION['statistic_search_data'] = empty($_SESSION['statistic_search_data']) ? array() : $_SESSION['statistic_search_data'];
        $statistic_search_data = & $_SESSION['statistic_search_data'];

        if ($action == 'reset_search' || empty($statistic_conditions)) {
            $statistic_conditions = " (actions.amount != 0) ";
            $statistic_search_data = array();
        }

        $statistic_conditions = 'action="sale" ';
        if (empty($_REQUEST['statistic_search'])) {
            $statistic_search = array();
        } else {
            $statistic_search = $_REQUEST['statistic_search'];
        }

        $statistic_search_data = (empty($search_type) || $search_type != 'add') ? $statistic_search : fn_array_merge($statistic_search_data, $statistic_search);

        if (AREA == 'C') {
            $statistic_conditions .= db_quote(" AND (actions.partner_id = ?i)", $auth['user_id']);
        } elseif (!empty($statistic_search_data['partner_id'])) {
            $statistic_conditions .= db_quote(" AND (actions.partner_id = ?i)", $statistic_search_data['partner_id']);
        }

        if (empty($_REQUEST['report_type']) || !in_array($_REQUEST['report_type'] , ['all', 'agent', 'subagent'] ) ) {
            $_REQUEST['report_type'] = 'agent';
        }
        if ($_REQUEST['report_type'] == 'agent') {
            $statistic_conditions .= " AND actions.partner_id = actions.customer_id ";
        } elseif ($_REQUEST['report_type'] == 'subagent') {
            $statistic_conditions .= " AND actions.partner_id != actions.customer_id ";
        }
        $view->assign('report_type', $_REQUEST['report_type']);

        if (!empty($_REQUEST['period']) && $_REQUEST['period'] != 'A') {
            list($_REQUEST['time_from'], $_REQUEST['time_to']) = fn_create_periods($_REQUEST);
            $statistic_search_data['period'] = $_REQUEST['period'];
            if ($_REQUEST['period'] == 'C') {
                $statistic_search_data['start_date'] = $_REQUEST['time_from'];
                $statistic_search_data['end_date'] = $_REQUEST['time_to'];
            }

            $statistic_conditions .= db_quote(" AND (date >= ?i AND date <= ?i)", $_REQUEST['time_from'], $_REQUEST['time_to']); // FIXME
        } else {
            $statistic_search_data['period'] = 'A';
        }

        if (!empty($statistic_search_data['plan_id'])) {
            $statistic_conditions .= db_quote(" AND (actions.plan_id = ?i) ", $statistic_search_data['plan_id']);
        }
        if (!empty($statistic_search_data['payout_id'])) {
            $_conditions = '';
            foreach ($statistic_search_data['payout_id'] as $_act) {
                $_conditions .= (empty($_conditions) ? '' : 'OR') . db_quote(" (action = ?s) ", $_act);
            }
            $statistic_conditions .= " AND ($_conditions) ";
        }
        if (!empty($statistic_search_data['status'])) {
            $_conditions = '';
            foreach ($statistic_search_data['status'] as $_status) {
                $_conditions .= empty($_conditions) ? '' : 'OR';
                if ($_status == 'P') {
                    $_conditions .= " (actions.payout_id != 0) ";
                } elseif ($_status == 'A') {
                    $_conditions .= " (actions.payout_id = 0 AND actions.approved = 'Y') ";
                } else {
                    $_conditions .= " (actions.approved = 'N' AND actions.payout_id = 0) ";
                }
            }
            $statistic_conditions .= " AND ($_conditions) ";
        } else {
            $statistic_conditions .= ''; //" AND actions.payout_id != 0 ";
        }
        if (!empty($statistic_search_data['zero_actions']) && $statistic_search_data['zero_actions'] == 'Y' && AREA != 'C') {
            $statistic_conditions .= " AND (actions.amount = 0) ";
        } elseif (empty($statistic_search_data['zero_actions']) || AREA == 'C') {
            $statistic_conditions .= " AND (actions.amount != 0) ";
        }
        $statistic_search_data['amount_from'] = empty($statistic_search_data['amount_from']) ? 0 : floatval($statistic_search_data['amount_from']);
        if (!empty($statistic_search_data['amount_from'])) {
            $statistic_conditions .= db_quote(" AND (actions.amount >= ?d) ", fn_convert_price($statistic_search_data['amount_from']));
        }
        $statistic_search_data['amount_to'] = empty($statistic_search_data['amount_to']) ? 0 : floatval($statistic_search_data['amount_to']);
        if (!empty($statistic_search_data['amount_to'])) {
            $statistic_conditions .= db_quote(" AND (actions.amount <= ?d) ", fn_convert_price($statistic_search_data['amount_to']));
        }

        $view->assign('statistic_search', $statistic_search_data);

    $joins = array();
    $order_status_join = '';
    $payout_join = '';
    $product_join = '';
    $std_payout_join = '';
    if (!empty($_REQUEST['order_status'])) {
        $order_status = $_REQUEST['order_status'];
        $joins[] = $order_status_join = db_process (' JOIN ?:aff_action_links al ON al.action_id = actions.action_id AND al.object_type = "O" JOIN ?:orders o ON o.order_id = al.object_data AND o.status = ?s', array($order_status));
    }
    if (!empty($_REQUEST['paid_date_from']) || !empty($_REQUEST['paid_date_to']) ) {
        $payout_join = db_process(' JOIN ?:affiliate_payouts ap ON ap.payout_id = actions.payout_id ');
        if (!empty($_REQUEST['paid_date_from']) ) {
            $payout_join .= db_process(' AND ap.date >= ?i ', array(strtotime($_REQUEST['paid_date_from'] ) ) );
//            $payout_join .= db_process(' AND ap.date >= ?s ', array(date('Y-m-d G:i:s' , strtotime($_REQUEST['paid_date_from']) ) ) );
        }
        if (!empty($_REQUEST['paid_date_to']) ) {
            $payout_join .= db_process(' AND ap.date <= ?i ', array(strtotime($_REQUEST['paid_date_to'] ) ) );
        }
        $joins[] = $payout_join;
    }

    if (!empty ($_REQUEST['product_id']) || !empty ($_REQUEST['company_id'])) {
        if (!empty($order_status_join)) {
            $product_join = db_process('JOIN ?:order_details od ON od.order_id = o.order_id ');
        } else {
            $product_join = db_process(' JOIN ?:aff_action_links al ON al.action_id = actions.action_id AND al.object_type = "O" JOIN ?:orders o ON o.order_id = al.object_data JOIN ?:order_details od ON od.order_id = o.order_id ');
        }
        if (!empty ($_REQUEST['product_id']))  {
            $product_join .= db_process(' AND od.product_id = ?i ', array($_REQUEST['product_id']));
        }

        if (!empty ($_REQUEST['company_id']))  {
            $product_join .= db_process(' JOIN ?:products p ON p.product_id=od.product_id AND p.company_id = ?i ', array($_REQUEST['company_id']));
        }

        $joins[] = $product_join;
    }
    if (!empty ($_REQUEST['customer_id']) ) {
        $statistic_conditions .= db_process(' AND actions.customer_id = ?i ', array($_REQUEST['customer_id']));
    }

    if ( empty($payout_join)) {
        $joins[] = $std_payout_join = db_process(' LEFT JOIN ?:affiliate_payouts ap ON ap.payout_id = actions.payout_id ');
    }

//        $general_stats['total'] = db_get_row("SELECT action, IF(ap.status = 'S', ap.amount, 0) as amount, COUNT(action) as count, SUM(actions.amount) as sum, AVG(actions.amount) as avg, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions GROUP BY action",  $order_status_join, $payout_join, $product_join, $std_payout_join);
        $general_stats['total'] = db_get_row("SELECT action, COUNT(action) as count, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions GROUP BY action",  $order_status_join, $payout_join, $product_join, $std_payout_join);
        if($_REQUEST['report_type'] == 'all') {
/*            $general_stats['you'] = db_get_row("SELECT action, IF(ap.status = 'S', ap.amount, 0) as amount, COUNT(action) as count, SUM(actions.amount) as sum, AVG(actions.amount) as avg, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions AND actions.partner_id = actions.customer_id GROUP BY action", $order_status_join, $payout_join, $product_join, $std_payout_join);
            $general_stats['collegues'] = db_get_row("SELECT action, IF(ap.status = 'S', ap.amount, 0) as amount, COUNT(action) as count, SUM(actions.amount) as sum, AVG(actions.amount) as avg, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions AND actions.partner_id != actions.customer_id GROUP BY action", $order_status_join, $payout_join, $product_join, $std_payout_join);*/
            $general_stats['you'] = db_get_row("SELECT action, COUNT(action) as count, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions AND actions.partner_id = actions.customer_id GROUP BY action", $order_status_join, $payout_join, $product_join, $std_payout_join);
            $general_stats['collegues'] = db_get_row("SELECT action, COUNT(action) as count, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions AND actions.partner_id != actions.customer_id GROUP BY action", $order_status_join, $payout_join, $product_join, $std_payout_join);
        }

//        $general_stats['total'] = db_get_row("SELECT 'total' as action, COUNT(action) as count, SUM(actions.amount) as sum, AVG(actions.amount) as avg, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions as actions WHERE $statistic_conditions");


        $list_plans = fn_get_affiliate_plans_list();
        $view->assign('list_plans', $list_plans);

        $view->assign('affiliate_plan', fn_get_affiliate_plan_data_by_partner_id($auth['user_id']));

        $sort_order = empty($_REQUEST['sort_order']) ? 'desc' : $_REQUEST['sort_order'];
        $sort_by = empty($_REQUEST['sort_by']) ? 'date' : $_REQUEST['sort_by'];

        $list_stats = fn_get_affiliate_actions(array(
            'prepared' => $_SESSION['statistic_conditions'],
            'order_status' => $_REQUEST['order_status'],
            'join' => $joins
        ), array('sort_order' => $sort_order, 'sort_by' => $sort_by), false, @$_REQUEST['page']);


        $view->assign('order_status', $_REQUEST['order_status']);
        $view->assign('sort_order', $sort_order == 'asc' ? 'desc' : 'asc');
        $view->assign('sort_by', $sort_by);

        foreach($list_stats as &$sale) {
            $sale_order  = fn_agents_get_orders(null, array('where' => array('order_id' => $sale['data']['O'])));
            $sale['order'] = $sale_order[0];
            $sale['payout_date'] = fn_agents_get_payout_date($sale['payout_id'], false);
            if ($sale['order']['status'] == 'C') {
                $general_stats['total']['sum'] += $sale['amount'];
                if($sale['partner_id'] == $sale['customer_id']) {
                    $general_stats['you']['sum'] += $sale['amount'];
                } else {
                    $general_stats['collegues']['sum'] += $sale['amount'];
                }
            } else {
                $sale['amount'] = 0;
            }

        }
        unset($sale);
    if ($_REQUEST['report_type'] !== 'all') {
        unset($general_stats['you']);
        unset($general_stats['collegues']);
    }


        if (!empty($_REQUEST['post_sort_by']) ) {
            $psort = $_REQUEST['post_sort_by'];
            $sorted_list_stats = array();

            if (true || $psort == 'order') {
                $order_ids = array();
                $index = 0;
                foreach($list_stats as $sale) {
                    if ($psort == 'order') {
                        $order_ids[$sale['order']['order_id']][] = $index;
                    } elseif ($psort == 'company') {
                        $order_ids[
                            preg_replace(
                                '/[^\w^\d]/u', '_',
                                $sale['order']['company_data']['company']
                            )][] = $index;
                    } elseif ($psort == 'product') {
                        $order_ids[
                        preg_replace(
                            '/[^\w^\d]/u', '_',
                            $sale['order']['product_data']['product']
                        )][] = $index;
                    } elseif ($psort == 'agent' || $psort == 'subagent') {
                        if($sale['partner_id'] == $sale['customer_id']) {
                            $order_ids['agent'][
                            preg_replace(
                                '/[^\w^\d]/u', '_',
                                $sale['customer_lastname']
                            )][] = $index;
                        } else {
                            $order_ids['subagent'][
                            preg_replace(
                                '/[^\w^\d]/u', '_',
                                $sale['customer_lastname']
                            )][] = $index;
                        }
                    } elseif ($psort == 'sum') {
                        $order_ids[$sale['order']['total']][] = $index;
                    } elseif ($psort == 'status') {
                        $order_ids[
                        preg_replace(
                            '/[^\w^\d]/u', '_',
                            $sale['order']['status_description']
                        )][] = $index;
                    } elseif ($psort == 'agent_profit' || $psort == 'subagent_profit') {
                        if($sale['partner_id'] == $sale['customer_id']) {
                            $order_ids['agent'][$sale['amount']][] = $index;
                        } else {
                            $order_ids['subagent'][$sale['amount']][] = $index;
                        }
                    } elseif ($psort == 'paid_date') {
                        $order_ids[$sale['payout_date']][] = $index;
                    }
                    $index++;
                }

                $order_ids = is_array($order_ids) ? $order_ids : array();
                $order_ids['agent'] = is_array($order_ids['agent']) ? $order_ids['agent'] : array();
                $order_ids['subagent'] = is_array($order_ids['subagent']) ? $order_ids['subagent'] : array();

                if ($_REQUEST['sort_order'] == 'desc') {
                    if ($psort == 'agent' || $psort == 'agent_profit') {
                        krsort($order_ids['agent']);
                        krsort($order_ids['subagent']);
                        $order_ids = array_merge(
                            $order_ids['agent'],
                            $order_ids['subagent']
                        );
                    } elseif ($psort == 'subagent' || $psort == 'subagent_profit') {
                        krsort($order_ids['agent']);
                        krsort($order_ids['subagent']);
                        $order_ids = array_merge(
                            $order_ids['subagent'],
                            $order_ids['agent']
                        );
                    } else {
                        krsort($order_ids);
                    }
                } else {
                    if ($psort == 'agent' || $psort == 'agent_profit') {
                        ksort($order_ids['agent']);
                        ksort($order_ids['subagent']);
                        $order_ids = array_merge(
                            $order_ids['agent'],
                            $order_ids['subagent']
                        );
                    } elseif ($psort == 'subagent' || $psort == 'subagent_profit') {
                        ksort($order_ids['agent']);
                        ksort($order_ids['subagent']);
                        $order_ids = array_merge(
                            $order_ids['subagent'],
                            $order_ids['agent']
                        );
                    } else {
                        ksort($order_ids);
                    }
                }
                foreach($order_ids as $ords) {
                    foreach($ords as $index) {
                        $sorted_list_stats[] = array_values($list_stats)[$index];
                    }
                }
                $list_stats = $sorted_list_stats;
            }
        }

        if (!empty($list_stats)) {
            $list_stats_count = count($list_stats);
            if (!empty($_REQUEST['page']) ) {
                $view->assign('list_stats', array_slice($list_stats, (intval($_REQUEST['page']) - 1) * $limit, $limit, true) );
                fn_paginate($_REQUEST['page'], $list_stats_count);
            } else {
                $view->assign('list_stats', array_slice($list_stats, 0, $limit, true) );
                fn_paginate(1, $list_stats_count);
            }
        }

        $order_status_descr = fn_get_statuses(STATUSES_ORDER, true, true, true);
        $view->assign('order_status_descr', $order_status_descr);
        $view->assign('order_statuses', fn_agents_get_order_statuses());
        $view->assign('products', fn_agents_get_products(array('company_id' => $_REQUEST['company_id']))[0]);
        $companies = fn_get_companies(null, $auth);
        $view->assign('companies', $companies[0]);
        $view->assign('company_id', $_REQUEST['company_id']);
        $view->assign('product_id', $_REQUEST['product_id']);
        $view->assign('general_stats', $general_stats);




    if ($mode == 'report_export') {
// Подключаем класс для работы с excel
        require_once(DIR_LIB . 'phpexcel/Classes/PHPExcel.php');
// Подключаем класс для вывода данных в формате excel
        require_once(DIR_LIB . 'phpexcel/Classes/PHPExcel/Writer/Excel5.php');

// Создаем объект класса PHPExcel
        $xls = new PHPExcel();
// Устанавливаем индекс активного листа
        $xls->setActiveSheetIndex(0);
// Получаем активный лист
        $sheet = $xls->getActiveSheet();

        $user = fn_get_user_info($auth['user_id'], false);
// Подписываем лист
        $report_title = fn_get_lang_var('report_for_agent') . " #" . $user['user_id'] . ' ' . $user['lastname'] . ' '. $user['firstname'] . ' ';
        $sheet->setTitle(mb_substr($report_title,0, 31));
// Вставляем текст в ячейку A1
        $sheet->setCellValue("A1", $report_title);
        $sheet->getStyle('A1')->getFill()->setFillType(
            PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');

        $is_full_report = $_REQUEST['report_type'] == 'all';
// Объединяем ячейки
        $sheet->mergeCells('A1:H1');

// Выравнивание текста
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//Report date
        $sheet->setCellValue("A2", fn_get_lang_var("Date"));
        $sheet->setCellValue("B2", fn_date_format(time()));
//General statistics
        $sheet->setCellValue("A4", fn_get_lang_var("general_statistics"));
        $sheet->mergeCells('A4:D4');
        $col = 0;
        $row = 5;
        if($is_full_report) {
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("profit_source"));
        }
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("orders_count"));
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("agent_total_profit"));
//        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("agent_average_profit"));
        $row++;
        $col = 0;
        foreach($general_stats as $profit_source => $g_st) {
            if($is_full_report) {
                $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var($profit_source));
            }
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['count']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['sum']);
//            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['avg']);
            $row++;
            $col = 0;
        }
        $row++;
//Sales list
        $sheet->setCellValue("A$row", fn_get_lang_var("details"));
        $sheet->mergeCells("A$row:K$row");
        $row++;

        $col = 0;
        $agent_included = $is_full_report || $_REQUEST['report_type'] == 'agent';
        $subagent_included = $is_full_report || $_REQUEST['report_type'] == 'subagent';
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("Order")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("company")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("product")); $col++;
        if ($agent_included) {
            $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("agent"));
            $col++;
        }
        if ($subagent_included) {
            $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("subagent")); $col++;

        }
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("price")); $col++;
        if ($agent_included) {
            $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("agent_profit")); $col++;
        }
        if ($subagent_included) {
            $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("agent_profit_from_subagent")); $col++;
        }
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("status")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("registration_date")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("paid_date"));
        $row++;

        $col = 0;
        foreach($list_stats as $sale) {
            $order = fn_agents_get_orders(null, array('where' => array('order_id' => $sale['data']['O'])));
            $order = $order[0];
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['order_id']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['company_data']['company']);
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['product_data']['product']);

            if ($agent_included) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $sale['partner_id'] == $sale['customer_id'] ? fn_get_user_name( $sale['partner_id']) : '');
            }

            if ($subagent_included) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $sale['partner_id'] == $sale['customer_id'] ? '' : fn_get_user_name($sale['customer_id']));
            }
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['total']);

            if ($agent_included) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $sale['partner_id'] == $sale['customer_id'] ? $sale['amount'] : 0);
            }
            if ($subagent_included) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $sale['partner_id'] == $sale['customer_id'] ? 0 : $sale['amount']);
            }

            $sheet->setCellValueByColumnAndRow($col++, $row, $sale['order']['status_description']);
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_date_format($sale['date']));
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_agents_get_payout_date($sale['payout_id']));
            $row++;
            $col = 0;
        }

        foreach(range('A','N') as $columnID) {
            $xls->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        $xls->getActiveSheet()->getStyle('A1:' . 'N' . $row)->getAlignment()->setIndent(0);

        $xls->getActiveSheet()->getStyle('A1:' . 'N' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(TRUE);


        $report_name = date('m_d_', time()) . 'report_user_' . $user['user_id'] . '.xls';

        // Выводим HTTP-заголовки
        header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ( "Content-type: application/vnd.ms-excel" );
        header ( "Content-Disposition: attachment; filename=" . $report_name );

        PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
// Выводим содержимое файла
        try {
            $objWriter = new PHPExcel_Writer_Excel5($xls);
        } catch (Exception $e) {
            var_dump($e);
            die();
        }
        if(!is_dir(DIR_CUSTOM_FILES . 'reports/')) {
            mkdir(DIR_CUSTOM_FILES . 'reports/');
            chmod(DIR_CUSTOM_FILES . 'reports/', DEFAULT_DIR_PERMISSIONS);
        }
        try {
            $objWriter->save('php://output');
//            $objWriter->save(DIR_CUSTOM_FILES . 'reports/' . $report_name);
        } catch (Exception $e) {
            var_dump($e);
            die();
        }
        return array(CONTROLLER_STATUS_OK);



    }
}


elseif (in_array($mode, array('orders', 'orders_active', 'orders_closed'  ))  ) {
    $uid = empty($_REQUEST['where']['user_id']) ? $auth['user_id'] : $_REQUEST['where']['user_id'];
    $order_statuses = 0;
   switch ($mode) {
       case 'orders' :
           $orders = fn_agents_get_orders($uid, $_REQUEST);
           $pagination = fn_agents_paginate_orders($uid, $_REQUEST, $limit, $page);
           $order_statuses = fn_agents_get_order_statuses();
           break;
       case 'orders_active' :
           $orders = fn_agents_get_active_orders($uid, $_REQUEST);
           $pagination = fn_agents_paginate_active_orders($uid, $_REQUEST, $limit, $page);
           $order_statuses = fn_agents_get_active_order_statuses();
           break;
       case 'orders_closed' :
           $orders = fn_agents_get_closed_orders($uid, $_REQUEST);
           $pagination = fn_agents_paginate_closed_orders($uid, $_REQUEST, $limit, $page);
           break;
   }
    $products = fn_agents_get_products(array('company_id'=>$_REQUEST['where']['company_id'] ), null, CART_LANGUAGE, null, false );
    $companies = fn_get_companies(null, $auth);
    $offices = fn_agents_get_company_offices(null);
    $cities = fn_agents_extract_cities_from_offices($offices);
    $view->assign('order_statuses', $order_statuses);
    $view->assign('all_cities', $cities);
    $view->assign('filter_city', $_REQUEST['filter_city']);
    $view->assign('products', $products[0]);
    $view->assign('companies', $companies[0]);
    $view->assign('where', $_REQUEST['where'] );
    $view->assign('order', $_REQUEST['order'] );
    $view->assign('orders', $orders );
    $view->assign('pagination', $pagination);
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'orders_saved') {
    $products = fn_agents_get_saved_products($auth['user_id'], $_REQUEST);
    $affiliate_plan = fn_get_affiliate_plan_data_by_partner_id($auth['user_id']);
    foreach($products as &$product) {
        $product['image']['image_path'] = get_image_full_path($product['image']);
        $product['company'] = fn_agents_get_company_info($product['company_id']);
        $product['company']['image_path'] =
            ( ( $fullpath = get_image_full_path($product['company']) ) == PATH_NO_IMAGE ) ?
                fn_agents_get_company_logo($product['company_id']) :
                get_image_full_path($product['company']) ;
        $product['description'] = fn_agents_get_product_description($product['product_id']);
        $product['profit'] = fn_agents_get_plan_product_profit($affiliate_plan, $product);
    }
    unset($product);
    $pagination = fn_agents_paginate_saved_products($auth['user_id'], $_REQUEST, $limit, $page);
    $companies = fn_get_companies(null, $auth);

//    $cities = fn_agents_get_all_cities($_REQUEST);
    $view->assign('order_statuses', fn_agents_get_order_statuses());
    $view->assign('all_cities', $cities);
    $view->assign('products', $products);
    $view->assign('companies', $companies[0]);
    $view->assign('where', $_REQUEST['where'] );
    $view->assign('pagination', $pagination);
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'order_make') {

    $step = empty($_REQUEST['step']) ? 1 : intval($_REQUEST['step'])+1 ;
    if(empty($_REQUEST['client']['notify'])) {
        $email_fields = array();
    } else {
        $email_fields = array('email');
    }
    $errors = fn_agents_get_client_fields_errors($_REQUEST['client'],
        array(
            'email_fields' => $email_fields,
            'not_empty_fields' => array(
                'default' => true,
                'office'
            ),
            'integer_fields' => array(
                'default' => true,
                'office'
            ),
        )
    );

    if ($step == 2) {
        if(!empty($errors)){
            fn_agents_display_errors($errors);
            $step = 1;
        } else {
            if(!empty($_FILES)) {
                $file = $_FILES['order_file'];
                $path = DIR_CUSTOM_FILES . 'order_files/' . $file['name'];
                if(!is_dir(dirname($path))) {
                    mkdir(dirname($path));
                    chmod(dirname($path), 0777);
                }
                $tmp_name = $file['tmp_name'];
                if (is_uploaded_file($tmp_name )) {
                    if(move_uploaded_file($tmp_name , $path)) {
                        $_REQUEST['order_file'] = $path;
                        Registry::get('view')->assign('order_file', $path);
                    } else {
                        fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('file_not_uploaded'));
                        $step = 1;
                    }
                }
            }
                fn_agents_process_order($_REQUEST, $step, $auth);
        }
    }
    if ($step == 3) {
        fn_agents_process_order($_REQUEST, $step, $auth);
    }
    if(!empty($_REQUEST['order_id'])) {
        $order = fn_agents_get_orders($auth['user_id'], array('where' => array('order_id' => $_REQUEST['order_id'])));
        $order = $order[0];
        $_REQUEST['product_id'] = $order['product_id'];
    }
    $product = fn_agents_get_products(array('product_id' => $_REQUEST['product_id']));
    $product = $product[0][0];
    $company_id = $product['company_id'];
//    $regions = fn_agents_get_all_regions();
    $product_offices = fn_agents_get_product_offices_ids($_REQUEST['product_id']);
    $regions = fn_agents_get_company_offices_with_regions($company_id, array('office_id' => $product_offices));
    $view->assign('regions', $regions );
    $companies = fn_get_companies(array('company_id' => $company_id), $auth);
    $view->assign('companies', $companies[0]);
    $view->assign('company', $companies[0][0]);
//    $cities = fn_agents_get_all_cities($_REQUEST['client']);
    $view->assign('cities', $regions);
    $offices = fn_agents_get_company_offices_with_shippings($company_id, array('office_id' => $product_offices) );
    if(empty($offices)) {
        fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('company_have_no_offices'));
        return array(CONTROLLER_STATUS_REDIRECT, 'agents.companies_and_products' );
    }
    $view->assign('offices' , $offices);
    $view->assign('step', $step );
    $view->assign('product', array(
            'product_id' => $_REQUEST['product_id'],
            'amount' => ($_REQUEST['item_count'] || 1) )
    );
    $locations = $_REQUEST['client']['locations'] = fn_agents_process_order_address($_REQUEST['client']);
    $_REQUEST['client']['address'] = fn_agents_locations_to_address($locations, true);
    $view->assign('client', empty($_REQUEST['client']) ? array() : $_REQUEST['client']);
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'order_save') {
    if (empty($_SESSION['cart'])) {
        fn_clear_cart($_SESSION['cart']);
    }

    $cart = & $_SESSION['cart'];
    fn_clear_cart($cart);
    fn_agents_add_affiliate_data_to_cart($cart, $auth);

    $product_data = array($_REQUEST['product_id'] => array(
        'product_id' => $_REQUEST['product_id'],
        'amount' => 1
    ));
    fn_update_product_amount($product_data['product_id'], 1, null, '+');
    fn_add_product_to_cart($product_data, $cart, $auth);
    fn_save_cart_content($cart, $auth['user_id']);
    fn_calculate_cart_content($cart, $auth, 'S', true, 'F', true);
    fn_agents_save_order($cart, $auth);
    fn_clear_cart($cart);
    fn_save_cart_content($cart, $auth['user_id']);


    $view->assign('product', $product );
    $view->assign('client', empty($_REQUEST['client']) ? array() : $_REQUEST['client']);
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'product_info' || $mode == 'company_info') {
    if (empty ($_REQUEST['product_id']) && empty ($_REQUEST['company_id'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    if (!empty ($_REQUEST['product_id'])) {
        $product = fn_agents_get_products(array('product_id' => $_REQUEST['product_id'], 'pid' => $_REQUEST['product_id']) );
        $product = $product[0][0];
        if(empty ($product)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
        $product['image']['image_path'] = get_image_full_path($product['image']);
        $product['description'] = fn_agents_get_product_description($_REQUEST['product_id']);
        $company = fn_agents_get_company_info($product['company_id']);
    } else {
        $product = array();
        $company = fn_agents_get_company_info($_REQUEST['company_id']);
        if(empty($company)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
    }
    $c_products = fn_agents_get_products(array ('company_id' => $company['company_id']));
    $c_products = $c_products[0];
    foreach ($c_products as &$c_product) {
//        fn_get_image_pairs($c_product['product_id'], 'product', 'A');
        $c_product['image']['image_path'] = get_image_full_path($c_product['image'], 0, 80, 80);
        $c_product['description'] = fn_agents_get_product_description($_REQUEST['product_id']);
    }
    unset ($c_product);
    $company['image_path'] = fn_agents_get_company_logo($company['company_id']);
    if($mode == 'product_info') {
        $offices = fn_agents_get_company_offices_with_shippings($company['company_id'], array('office_id'=> fn_agents_get_product_offices_ids($_REQUEST['product_id'])));
    } else {
        $offices = fn_agents_get_company_offices_with_shippings($company['company_id']);
    }
    $cities = fn_agents_extract_cities_from_offices($offices);
    $view->assign('order_statuses', fn_agents_get_order_statuses());
    $view->assign('mode', 'product_info');
    $view->assign('product', $product);
    $view->assign('company', $company);
    $view->assign('offices', $offices);
    $view->assign('cities', $cities);
    $view->assign('all_products', $c_products);


    if($mode == 'product_info') {
        $active_tab = 'product';
    } else {
        $active_tab = 'company';
    }
    $active_tab = empty($_REQUEST['active_tab']) ? $active_tab : $_REQUEST['active_tab'];
    $view->assign('active_tab', $active_tab);

    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'update_client') {
    if(empty($_REQUEST["profile_id"]) || empty($_REQUEST["field"]) || empty($_REQUEST["value"]) ) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    $field = $_REQUEST['field'];
    $profile_id = $_REQUEST["profile_id"];
    $value = $_REQUEST["value"];
    $errors = fn_agents_get_field_errors($field, $value);
    if (!empty($errors)) {
        echo(json_encode(array('status'=>'error',
        'data' => $errors)));
        die();
    }
    $res = db_query(db_process("UPDATE ?:user_profiles SET $field = ?s WHERE profile_id = ?i", array($value, $profile_id)  ));
    if($res) {
        echo(json_encode(array('status'=>'OK')));
       die();
    }
    return array(CONTROLLER_STATUS_NO_PAGE);
}
elseif ($mode == 'ajax_get_offices') {
    $company_id = $_REQUEST['company_id'];
    $city_id = $_REQUEST['city_id'];
    $offices = fn_agents_get_company_offices_with_shippings($company_id, array('city_id' => $city_id) );
    if(empty($offices)) {
        echo (json_encode(array('status' => 'empty')));
    }
    if(!empty($_REQUEST['is_options']) ) {
        $ajaxResult = fn_agents_prepare_ajax_options($offices, 'office_id', 'office_name');
        echo json_encode(array('status' => 'OK', 'data' => $ajaxResult) );
    } else {
        $offices['length'] = count($offices);
        echo json_encode(array('status' => 'OK', 'data' => $offices) );
    }
    die();
}
elseif ($mode == 'ajax_get_cities') {
    $company_id = $_REQUEST['company_id'];
    $region_id = $_REQUEST['region_id'];
    $cities = fn_agents_get_company_offices($company_id, array('region_id' => $region_id) );
    if(empty($cities)) {
        echo (json_encode(array('status' => 'empty')));
        die();
    }
    $ajaxResult = fn_agents_prepare_ajax_options($cities, 'city_id', 'city');
    echo json_encode(array('status' => 'OK', 'data' => $ajaxResult) );
    die();
}
elseif ($mode == 'ajax_get_regions') {
    $company_id = $_REQUEST['company_id'];
    $regions = fn_agents_get_company_offices_with_regions($company_id);
    if(empty($regions)) {
        echo (json_encode(array('status' => 'empty')));
        die();
    }
    $ajaxResult = fn_agents_prepare_ajax_options($regions, 'region_id', 'region');
    echo json_encode(array('status' => 'OK', 'data' => $ajaxResult) );
    die();
}
elseif ($mode == 'ajax_get_products') {
    $company_id = $_REQUEST['company_id'];
//    if(empty($company_id) ) {
//        return array(CONTROLLER_STATUS_NO_PAGE);
//    }
    $products = fn_agents_get_products(array('company_id' => $company_id));
    if(empty($products)) {
        echo (json_encode(array('status' => 'empty')));
        die();
    }
    $products = $products[0];
    $ajaxResult = fn_agents_prepare_ajax_options($products, 'product_id', 'product');
    echo json_encode(array('status' => 'OK', 'data' => $ajaxResult) );
    die();
}
elseif ($mode == 'ajax_save_product') {

    if(empty($_REQUEST['product_id']) || empty($auth['user_id'])) {
        echo (json_encode(array('status' => 'error')));
    } else {
        $query = db_process('SELECT * FROM ?:orders_saved WHERE user_id = ?i AND product_id = ?i', array($auth['user_id'], $_REQUEST['product_id']));
        $saved = db_get_array($query);
        if(empty($saved)) {
            db_query(db_process('INSERT INTO ?:orders_saved VALUES (NULL, ?i, ?i)', array($_REQUEST['product_id'], $auth['user_id'])) );
        }
        echo json_encode(array('status' => 'OK') );
    }
    die();
}
elseif ($mode == 'ajax_remove_saved_order') {

    if(empty($_REQUEST['product_id']) || empty($auth['user_id'])) {
        echo (json_encode(array('status' => 'error')));
    } else {
        $query = db_process('DELETE FROM ?:orders_saved WHERE user_id = ?i AND product_id = ?i', array($auth['user_id'], $_REQUEST['product_id']));
        $deleted = db_query($query);
        if(empty($deleted)) {
            echo (json_encode(array('status' => 'error')));
        }
        echo json_encode(array('status' => 'OK') );
    }
    die();
}
elseif ($mode == 'all_plans') {
    $plans = fn_agents_get_plans_logos();
    $view->assign('plans', $plans);
}

