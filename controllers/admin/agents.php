<?php


if ( !defined('AREA') )	{ die('Access denied');	}

if (empty($auth['user_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
}

include dirname(__FILE__) . "/../customer/fn_agents.php";


if ($mode == 'offices') {
    $cities = fn_agents_get_all_cities();
    if($_REQUEST['submit'] == 'submit') {
        $errors = fn_agents_get_office_fields_errors($_REQUEST['office']);
        if (empty($errors)) {
            $query = db_process("INSERT INTO ?:company_offices ?e", array($_REQUEST['office']) );
            db_query( $query);
        } else {
            fn_agents_display_errors($errors);
        }
    }
    Registry::get('view')->assign('cities', $cities);
    Registry::get('view')->assign('mode', 'offices');
    Registry::get('view')->assign('content_tpl', 'views/agents/offices.tpl');
    return array(CONTROLLER_STATUS_OK);
}

