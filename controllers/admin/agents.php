<?php


if ( !defined('AREA') )	{ die('Access denied');	}

if (empty($auth['user_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
}
$fnagentsphp = realpath(dirname(__FILE__) . "/../customer/fn_agents.php");
require_once $fnagentsphp;
$view = Registry::get('view');
if(empty($_REQUEST['company_id']) ) {
    if(!empty ($_REQUEST['office_id'])) {
        $office = fn_agents_get_company_offices(null, array('office_id' => $_REQUEST['office_id'] ) );
        $office = $office[0];
        $cid = $office['company_id'];
        Registry::get('view')->assign('office_id', $office['office_id']);
        Registry::get('view')->assign('office', $office);
    }
    if(empty($cid) && in_array($mode, array('offices', 'offices_add', 'office_shippings', 'office_shipping_add') ) ) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
}
else {
    $cid = $_REQUEST['company_id'];
}
if ($auth['user_type'] == 'V') {
    $cid = $_REQUEST['company_id'] = COMPANY_ID;
    $is_vendor = true;
} else {
    $is_vendor = false;
}
Registry::get('view')->assign('is_vendor', $is_vendor);


if (isset($cid)) {
    Registry::get('view')->assign('company_id', $cid);
}
$limit = $_REQUEST['limit'] = empty($_REQUEST['limit']) ? !empty($_REQUEST['items_per_page']) ? $_REQUEST['items_per_page'] : 10 : $_REQUEST['limit'];
$page = $_REQUEST['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];

if ($mode == 'offices') {

        Registry::get('view')->assign('mode', 'view');
        $offices = fn_agents_get_company_offices_with_shippings($cid);
        Registry::get('view')->assign('offices', $offices);


    Registry::get('view')->assign('content_tpl', 'views/agents/offices.tpl');
    return array(CONTROLLER_STATUS_OK);
}
elseif ($mode == 'offices_add') {
    Registry::get('view')->assign('mode', 'update');
    $redirect_url = null;

    $cities = fn_agents_get_all_cities();
    Registry::get('view')->assign('cities', $cities);


    if($_REQUEST['submit'] == 'submit') {
        fn_trusted_vars('office');
        $errors = fn_agents_get_office_fields_errors($_REQUEST['office']);
        if (empty($errors)) {
            $query = db_process("REPLACE INTO ?:company_offices ?e", array($_REQUEST['office']) );
            db_query( $query);
            fn_set_notification('N', fn_get_lang_var('information'),  fn_get_lang_var('text_office_is_created') );
            $redirect_url = fn_url('agents.offices') . "&company_id=$cid";
        } else {
            fn_agents_display_errors($errors);
        }
        Registry::get('view')->assign('office', $_REQUEST['office']);
    }
    Registry::get('view')->assign('content_tpl', 'views/agents/offices.tpl');
    return array(CONTROLLER_STATUS_OK, $redirect_url);

}
elseif ($mode == 'office_shippings') {
    $oid = $_REQUEST['office_id'];
    if (empty($oid)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    Registry::get('view')->assign('office_id', $oid);

    $redirect_url = null;
    $shippings = fn_agents_get_company_office_shippings($oid);
    Registry::get('view')->assign('shippings', $shippings);
    return array(CONTROLLER_STATUS_OK, $redirect_url);

}

elseif ($mode == 'office_shipping_add') {
    $oid = $_REQUEST['office_id'];
    if (empty($oid)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    Registry::get('view')->assign('office_id', $oid);
    $redirect_url = null;
    fn_trusted_vars('shipping');
    $shipping = $_REQUEST['shipping'];
    if($_REQUEST['submit'] == 'submit') {
        $errors = fn_agents_get_shipping_fields_errors($shipping);
        if (empty($errors)) {
            $query = db_process("INSERT INTO ?:company_office_shippings ?e", array($shipping) );
            db_query( $query);
            fn_set_notification('N', fn_get_lang_var('information'),  fn_get_lang_var('text_office_shipping_is_created') );
            $redirect_url = fn_url('agents.office_shippings') . "&office_id=$oid";
        } else {
            fn_agents_display_errors($errors);
        }
    }
    Registry::get('view')->assign('shipping', $shipping);
    return array(CONTROLLER_STATUS_OK, $redirect_url);
}

elseif ($mode == 'ajax_shipping_delete') {
    $sid = $_REQUEST['shipping_id'];
    if (empty($sid)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    $query = db_process('DELETE FROM ?:company_office_shippings WHERE shipping_id = ?i', array($sid));
    if (db_query($query)) {
        echo (json_encode(array('status' => 'OK')));
        die();
    }
    return array(CONTROLLER_STATUS_NO_PAGE);
}

elseif ($mode == 'ajax_office_delete') {
    $oid = $_REQUEST['office_id'];
    if (empty($oid)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    $query = db_process('DELETE FROM ?:company_office_shippings WHERE office_id = ?i', array($oid));
    if (db_query($query)) {
        $query = db_process('DELETE FROM ?:company_offices WHERE office_id = ?i', array($oid));
        if (db_query($query)) {
            echo (json_encode(array('status' => 'OK')));
            die();
        }
    }
    return array(CONTROLLER_STATUS_NO_PAGE);
}

elseif ($mode == 'sliders') {
    $sliders = fn_agents_get_sliders();
    if(isset($_REQUEST['submit'])) {
        if($_REQUEST['submit'] == 'submit') {
            fn_agents_add_slide();
        }
        elseif ($_REQUEST['submit'] == 'delete') {
            if(!empty($_REQUEST['slide_id']) ) {
                $query = db_process('DELETE FROM ?:slider_logos WHERE slide_id = ?i', array($_REQUEST['slide_id']));
                if( db_query($query) ) {
                    fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('text_changes_saved'));
                } else {
                    fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('error_ocured'));
                }
            }
        }
    }
    Registry::get('view')->assign('sliders', $sliders);
    Registry::get('view')->assign('mode', 'sliders');
    Registry::get('view')->assign('content_tpl', 'views/agents/sliders.tpl');
    $redirect_url = null;
    return array(CONTROLLER_STATUS_OK);
}

elseif ($mode == 'report' || $mode == 'report_export') {
    $collegues = fn_get_users(array('user_type'=>'P'), $auth);
    $collegues = $collegues[0];
    $collegues_ids = array();
    $collegues_map = array();
    $count_collegues = count($collegues);
    for ($i = 0; $i<$count_collegues; $i++) {
        $collegue = $collegues[$i];
        $collegues_ids[] = $collegue['user_id'];
        $collegues_map[$collegue['user_id']] = $i;
    }
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
        $_REQUEST['report_type'] = 'all';
    }
    if ($_REQUEST['report_type'] == 'agent') {
        $statistic_conditions .= " AND actions.partner_id = actions.customer_id ";
    } elseif ($_REQUEST['report_type'] == 'subagent') {
        $statistic_conditions .= " AND actions.partner_id != actions.customer_id ";
    }
    $view->assign('report_type', $_REQUEST['report_type']);
    if ($is_vendor) {
        $statistic_conditions .= " AND actions.partner_id = actions.customer_id ";
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

    $general_stats = db_get_hash_array("SELECT action, actions.partner_id,  COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions GROUP BY actions.partner_id ",  'partner_id', $order_status_join, $payout_join, $product_join, $std_payout_join);
//    $general_stats = db_get_hash_array("SELECT action, actions.partner_id, COUNT(action) as count, SUM(actions.amount) as sum, AVG(actions.amount) as avg, COUNT(distinct actions.partner_id) as partners FROM ?:aff_partner_actions  as actions ?p ?p ?p ?p WHERE $statistic_conditions GROUP BY actions.partner_id ",  'partner_id', $order_status_join, $payout_join, $product_join, $std_payout_join);
    foreach($general_stats as &$g_st) {
        $_collegue = $collegues[$collegues_map[$g_st['partner_id']]];
        $g_st['action'] = $_collegue['lastname'] . ' ' . $_collegue['firstname'];
    }
    unset($g_st);

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

    $company_profit_percents = array();
    foreach($list_stats as &$sale) {
        $sale_order  = fn_agents_get_orders(null, array('where' => array('order_id' => $sale['data']['O'])));
        $sale['order'] = $sale_order[0];
        if (false && !$is_vendor) {
            $sale['payout_date'] = fn_agents_get_payout_date($sale['payout_id'], false);
        } else {
            $sale['payout_date'] = $sale['order']['order_paid_date'];
        }
        if($sale['partner_id'] == $sale['customer_id'] && $sale['order']['status'] == 'C') {
            $general_stats[$sale['partner_id']]['site_profit'] += $sale['site_profit'] = fn_agents_get_site_order_profit($sale['order'], $company_profit_percents );
            $sale['pure_site_profit'] = doubleval($sale['site_profit']) - doubleval($sale['amount']);
        }
        if($sale['partner_id'] != $sale['customer_id'] && $sale['order']['status'] == 'C') {
            $sale['pure_site_profit'] = - doubleval($sale['amount']);
        }
        if ($sale['order']['status'] == 'C' ) {
            $general_stats[$sale['partner_id']]['count']++;
            if ($is_vendor) {
                $general_stats[$sale['partner_id']]['sum'] += doubleval($sale['order']['subtotal']);
            } else {
                $general_stats[$sale['partner_id']]['sum'] += $sale['amount'];
            }
//            $general_stats['total']['sum'] += $sale['amount'];
        } else {
            $sale['amount'] = 0;
        }
    }
    unset($sale);



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
                } elseif ($psort == 'agent') {
                    $order_ids[
                    preg_replace(
                        '/[^\w^\d]/u', '_',
                        $sale['partner_lastname'].'_'.$sale['partner_firstname']
                    )][] = $index;
                } elseif ($psort == 'subagent') {
                    if($sale['partner_id'] == $sale['customer_id']) {
                        $order_ids[0][] = $index;
                    } else {
                        $order_ids[
                        preg_replace(
                            '/[^\w^\d]/u', '_',
                            $sale['customer_lastname'].'_'.$sale['customer_firstname']
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
//            $order_ids['agent'] = is_array($order_ids['agent']) ? $order_ids['agent'] : array();
//            $order_ids['subagent'] = is_array($order_ids['subagent']) ? $order_ids['subagent'] : array();

            if ($_REQUEST['sort_order'] == 'desc') {
                if ($psort == 'agent' || $psort == 'agent_profit') {
                    krsort($order_ids['agent']);
                    krsort($order_ids['subagent']);
                    $order_ids = array_merge(
                        $order_ids['agent'],
                        $order_ids['subagent']
                    );
                } elseif ( $psort == 'subagent_profit') {
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
                if ( $psort == 'agent_profit') {
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
    $general_stats_total = array('action' => fn_get_lang_var('total'), 'sum' => 0, 'avg' => 0, 'site_profit' => 0);
    foreach ($general_stats as $g_st) {
        if($is_vendor) {
            $general_stats_total['sum'] += doubleval($g_st['sum']) - doubleval($g_st['site_profit']);
        } else {
            $general_stats_total['sum'] += doubleval($g_st['sum']);
        }
        $general_stats_total['site_profit'] += doubleval($g_st['site_profit']);
        $general_stats_total['count'] += intval($g_st['count']);
    }
    if($is_vendor) {
        $general_stats = array();
    }
    $general_stats['total'] = $general_stats_total;


    $order_status_descr = fn_get_statuses(STATUSES_ORDER, true, true, true);
    $view->assign('general_stats', $general_stats);
    $view->assign('order_status_descr', $order_status_descr);
    $view->assign('order_statuses', fn_agents_get_order_statuses());
    $view->assign('products', fn_agents_get_products(array('company_id' => $_REQUEST['company_id']))[0]);
    $companies = fn_get_companies(null, $auth);
    $view->assign('companies', $companies[0]);
    $view->assign('company_id', $_REQUEST['company_id']);
    $view->assign('product_id', $_REQUEST['product_id']);

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
        $sheet->setTitle(mb_substr($report_title, 0, 31));
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
        if($is_full_report && !$is_vendor) {
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("profit_source"));
        }
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("orders_paid_count"));
        if($is_vendor) {
          $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("total_profit"));
        }
        $sheet->setCellValueByColumnAndRow($col++, $row,  fn_get_lang_var($is_vendor ? "total_income" : "admin_report_agent_total_profit"));
        if (!$is_vendor) {
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("admin_pure_site_profit"));
        }
        if (!$is_vendor) {
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("admin_site_profit"));
        } else {
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("site_profit"));
        }
        $row++;
        $col = 0;
        foreach($general_stats as $profit_source => $g_st) {
            if($is_full_report && !$is_vendor) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['action']);
            }
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['count']);
          if ($is_vendor) {
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['site_profit']+$g_st['sum']);
          }
          $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['sum']);

            if (!$is_vendor) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['site_profit']-$g_st['sum']);
            }
            $sheet->setCellValueByColumnAndRow($col++, $row, $g_st['site_profit']);
            $row++;
            $col = 0;
        }
        $row++;
//Sales list
        $sheet->setCellValue("A$row", fn_get_lang_var("details"));
        $sheet->mergeCells("A$row:K$row");
        $row++;

        $col = 0;
        $agent_included = $is_vendor == false && $is_full_report || $_REQUEST['report_type'] == 'agent';
        $subagent_included = $is_vendor == false && $is_full_report || $_REQUEST['report_type'] == 'subagent';
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("Order")); $col++;
        if($is_vendor == false ) {
            $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("company")); $col++;
        }
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
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var($is_vendor?"site_profit":"admin_site_profit"));
        if(!$is_vendor) {
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("admin_pure_site_profit"));
        }
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("status")); $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, fn_get_lang_var("registration_date")); $col++;
        $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("paid_date"));
        if (!$is_vendor) {
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_lang_var("company_contract_id"));
        }
        $row++;

        $col = 0;
        foreach($list_stats as $sale) {
            $order = fn_agents_get_orders(null, array('where' => array('order_id' => $sale['data']['O'])));
            $order = $order[0];
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['order_id']);
            if (!$is_vendor) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $order['company_data']['company']);
            }
            $sheet->setCellValueByColumnAndRow($col++, $row, $order['product_data']['product']);

            if ($agent_included) {
                $sheet->setCellValueByColumnAndRow($col++, $row, fn_get_user_name( $sale['partner_id']));
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

            $sheet->setCellValueByColumnAndRow($col++, $row, $sale['site_profit']);
            if(!$is_vendor) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $sale['pure_site_profit']);
            }
            $sheet->setCellValueByColumnAndRow($col++, $row, $sale['order']['status_description']);
            $sheet->setCellValueByColumnAndRow($col++, $row, fn_date_format($sale['date']));
            $sheet->setCellValueByColumnAndRow($col++, $row, empty($sale['payout_date']) ? '' : fn_date_format(strtotime($sale['payout_date'])));
            if (!$is_vendor) {
                $sheet->setCellValueByColumnAndRow($col++, $row, $sale['order']['company_data']['company_contract_id']);
            }
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
elseif ($mode == 'product_offices') {
    $pid = intval($_REQUEST['product_id']);
    $oid = intval($_REQUEST['office_id']);
    $en = $_REQUEST['enabled'];
    if(empty($pid) || empty($oid) || empty($en)) {
        echo (json_encode(array('status' => 'error')));
        die();
//        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    if($en === 'true') {
        db_query("REPLACE INTO ?:product_offices (`product_id`, `office_id`) VALUES ( $pid , $oid)");
    } else {
        db_query("DELETE FROM  ?:product_offices WHERE `product_id` = $pid AND `office_id` = $oid ");
    }
    echo (json_encode(array('status' => 'ok')));
    die();
    $view->assign('product_id', $pid);
}

