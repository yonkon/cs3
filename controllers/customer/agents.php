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

            if (!empty($_REQUEST['return_url'])) {
                return array(CONTROLLER_STATUS_OK, $_REQUEST['return_url']);
            }

        } else {
            fn_save_post_data('user_data');
            fn_delete_notification('changes_saved');
        }

        if (!empty($user_id) && !$is_update) {
            $redirect_url = "agents.success_add";
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
if (strpos($mode, 'orders_') === 0) {
    $view->assign('mode', 'orders');
}

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
elseif ($mode == 'companies_and_products') {
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
    $view->assign('mode', 'products');
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
elseif ($mode == 'collegues') {
    if ( empty($_REQUEST['limit']) || !intval(($_REQUEST['limit']) )) {
        $limit = $_REQUEST['limit'] =  10;
    } else {
        $limit = $_REQUEST['limit'];
    }
    $page = $_REQUEST['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
    $pagination = fn_agents_paginate_collegues($auth['user_id'], $_REQUEST, $limit, $page);
    $collegues = fn_agents_get_collegues($auth['user_id'], $_REQUEST);
    $view->assign('collegues', $collegues);
    $view->assign('pagination', $pagination);
    $view->assign('client', $_REQUEST );
}

elseif ($mode == 'report') {
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
            $statistic_conditions = " (amount != 0) ";
            $statistic_search_data = array();
        }

        $statistic_conditions = 'action="sale" AND approved = "Y" ';
        if (empty($_REQUEST['statistic_search'])) {
            $statistic_search = array();
        } else {
            $statistic_search = $_REQUEST['statistic_search'];
        }

        $statistic_search_data = (empty($search_type) || $search_type != 'add') ? $statistic_search : fn_array_merge($statistic_search_data, $statistic_search);

        if (AREA == 'C') {
            $statistic_conditions .= db_quote(" AND (partner_id = ?i)", $auth['user_id']);
        } elseif (!empty($statistic_search_data['partner_id'])) {
            $statistic_conditions .= db_quote(" AND (partner_id = ?i)", $statistic_search_data['partner_id']);
        }
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
                    $_conditions .= " (payout_id != 0) ";
                } elseif ($_status == 'A') {
                    $_conditions .= " (payout_id = 0 AND actions.approved = 'Y') ";
                } else {
                    $_conditions .= " (actions.approved = 'N' AND payout_id = 0) ";
                }
            }
            $statistic_conditions .= " AND ($_conditions) ";
        }
        if (!empty($statistic_search_data['zero_actions']) && $statistic_search_data['zero_actions'] == 'Y' && AREA != 'C') {
            $statistic_conditions .= " AND (amount = 0) ";
        } elseif (empty($statistic_search_data['zero_actions']) || AREA == 'C') {
            $statistic_conditions .= " AND (amount != 0) ";
        }
        $statistic_search_data['amount_from'] = empty($statistic_search_data['amount_from']) ? 0 : floatval($statistic_search_data['amount_from']);
        if (!empty($statistic_search_data['amount_from'])) {
            $statistic_conditions .= db_quote(" AND (amount >= ?d) ", fn_convert_price($statistic_search_data['amount_from']));
        }
        $statistic_search_data['amount_to'] = empty($statistic_search_data['amount_to']) ? 0 : floatval($statistic_search_data['amount_to']);
        if (!empty($statistic_search_data['amount_to'])) {
            $statistic_conditions .= db_quote(" AND (amount <= ?d) ", fn_convert_price($statistic_search_data['amount_to']));
        }

        $view->assign('statistic_search', $statistic_search_data);

        $general_stats = db_get_hash_array("SELECT action, COUNT(action) as count, SUM(amount) as sum, AVG(amount) as avg, COUNT(distinct partner_id) as partners FROM ?:aff_partner_actions as actions WHERE $statistic_conditions GROUP BY action", 'action');

        $general_stats['total'] = db_get_row("SELECT 'total' as action, COUNT(action) as count, SUM(amount) as sum, AVG(amount) as avg, COUNT(distinct partner_id) as partners FROM ?:aff_partner_actions as actions WHERE $statistic_conditions");

        $view->assign('general_stats', $general_stats);
        $additional_stats = array();
        $additional_stats['click_vs_show'] = empty($general_stats['show']['count']) ? '---' : (empty($general_stats['click']['count']) ? '0' : round($general_stats['click']['count'] / $general_stats['show']['count'] * 100, 1) . '% (' . intval($general_stats['click']['count']) . '/' . intval($general_stats['show']['count']) . ')');
        $additional_stats['sale_vs_click'] = empty($general_stats['click']['count']) ? '---' : (empty($general_stats['sale']['count']) ? '0' : round($general_stats['sale']['count'] / $general_stats['click']['count'] * 100, 1) . '% (' . intval($general_stats['sale']['count']) . '/' . intval($general_stats['click']['count']) . ')');
        $view->assign('additional_stats', $additional_stats);

        $list_plans = fn_get_affiliate_plans_list();
        $view->assign('list_plans', $list_plans);

        $view->assign('affiliate_plan', fn_get_affiliate_plan_data_by_partner_id($auth['user_id']));

        $sort_order = empty($_REQUEST['sort_order']) ? 'desc' : $_REQUEST['sort_order'];
        $sort_by = empty($_REQUEST['sort_by']) ? 'date' : $_REQUEST['sort_by'];

        $list_stats = fn_get_affiliate_actions($_SESSION['statistic_conditions'], array('sort_order' => $sort_order, 'sort_by' => $sort_by), true, @$_REQUEST['page']);

        $view->assign('sort_order', $sort_order == 'asc' ? 'desc' : 'asc');
        $view->assign('sort_by', $sort_by);

        if (!empty($list_stats)) {
            $view->assign('list_stats', $list_stats);
        }

        $order_status_descr = fn_get_statuses(STATUSES_ORDER, true, true, true);
        $view->assign('order_status_descr', $order_status_descr);
        $view->assign('mode', 'report');


}
elseif ($mode == 'report_export') {
// Подключаем класс для работы с excel
    require_once(DIR_LIB.'PHPExcel.php');
// Подключаем класс для вывода данных в формате excel
    require_once('PHPExcel/Writer/Excel5.php');

// Создаем объект класса PHPExcel
    $xls = new PHPExcel();
// Устанавливаем индекс активного листа
    $xls->setActiveSheetIndex(0);
// Получаем активный лист
    $sheet = $xls->getActiveSheet();
// Подписываем лист
    $report_title = fn_get_lang_var('report_for_agent') . " #" . $auth['user_id'] . ' ' . $auth['lastname'] . ' '. $auth['firstname'] . ' ';
    $sheet->setTitle($report_title);

// Вставляем текст в ячейку A1
    $sheet->setCellValue("A1", $report_title);
    $sheet->getStyle('A1')->getFill()->setFillType(
        PHPExcel_Style_Fill::FILL_SOLID);
    $sheet->getStyle('A1')->getFill()->getStartColor()->setRGB('EEEEEE');

// Объединяем ячейки
    $sheet->mergeCells('A1:H1');

// Выравнивание текста
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(
        PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    for ($i = 2; $i < 10; $i++) {
        for ($j = 2; $j < 10; $j++) {
            // Выводим таблицу умножения
            $sheet->setCellValueByColumnAndRow(
                $i - 2,
                $j,
                $i . "x" .$j . "=" . ($i*$j));
            // Применяем выравнивание
            $sheet->getStyleByColumnAndRow($i - 2, $j)->getAlignment()->
                setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
    }


    // Выводим HTTP-заголовки
    header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
    header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
    header ( "Cache-Control: no-cache, must-revalidate" );
    header ( "Pragma: no-cache" );
    header ( "Content-type: application/vnd.ms-excel" );
    header ( "Content-Disposition: attachment; filename=report.xls" );

// Выводим содержимое файла
    $objWriter = new PHPExcel_Writer_Excel5($xls);
    $objWriter->save('php://output');
    die();
}

elseif (in_array($mode, array('orders', 'orders_saved', 'orders_active', 'orders_closed'  ))  ) {
    if ($mode != 'orders_saved') {
        $_REQUEST['where']['not'] = array ('status' => ORDER_STATUS_SAVED );
    } else {
        $view->assign('mode', $mode);
    }
   switch ($mode) {
       case 'orders' :
           $orders = fn_agents_get_orders($auth['user_id'], $_REQUEST);
           $pagination = fn_agents_paginate_orders($auth['user_id'], $_REQUEST, $limit, $page);
           break;
       case 'orders_saved' :
           $orders = fn_agents_get_saved_orders($auth['user_id'], $_REQUEST);
           $pagination = fn_agents_paginate_saved_orders($auth['user_id'], $_REQUEST, $limit, $page);
           break;
       case 'orders_active' :
           $orders = fn_agents_get_active_orders($auth['user_id'], $_REQUEST);
           $pagination = fn_agents_paginate_active_orders($auth['user_id'], $_REQUEST, $limit, $page);
           break;
       case 'orders_closed' :
           $orders = fn_agents_get_closed_orders($auth['user_id'], $_REQUEST);
           $pagination = fn_agents_paginate_closed_orders($auth['user_id'], $_REQUEST, $limit, $page);
           break;
   }
    $products = fn_agents_get_products(array('company_id'=>$_REQUEST['where']['company_id'] ), null, CART_LANGUAGE, null, false );
    $companies = fn_get_companies(null, $auth);
    $cities = fn_agents_get_all_cities($_REQUEST);
    $view->assign('order_statuses', fn_agents_get_order_statuses());
    $view->assign('products', $cities[0]);
    $view->assign('products', $products[0]);
    $view->assign('companies', $companies[0]);
    $view->assign('where', $_REQUEST['where'] );
    $view->assign('order', $_REQUEST['order'] );
    $view->assign('orders', $orders );
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
        }
        else{
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
    $regions = fn_agents_get_all_regions();
    $view->assign('regions', $regions );
    $companies = fn_get_companies(array('company_id' => $company_id), $auth);
    $view->assign('companies', $companies[0]);
    $cities = fn_agents_get_all_cities($_REQUEST['client']);
    $view->assign('cities', $cities);
    $offices = fn_agents_get_company_offices_with_shippings($company_id);
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

    $company['image_path'] = get_image_full_path($company);
    $offices = fn_agents_get_company_offices_with_shippings($company['company_id']);
    $cities = fn_agents_extract_cities_from_offices($offices);
    $view->assign('order_statuses', fn_agents_get_order_statuses());
    $view->assign('mode', 'product_info');
    $view->assign('product', $product);
    $view->assign('company', $company);
    $view->assign('offices', $offices);
    $view->assign('cities', $cities);


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
elseif ($mode == 'all_plans') {
    $plans = fn_agents_get_plans_logos();
    $view->assign('plans', $plans);
}

