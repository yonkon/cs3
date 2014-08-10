<?php


if ( !defined('AREA') )	{ die('Access denied');	}

if (empty($auth['user_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
}

include dirname(__FILE__) . "/../customer/fn_agents.php";

if(empty($_REQUEST['company_id']) ) {
    return array(CONTROLLER_STATUS_NO_PAGE);
}
$cid = $_REQUEST['company_id'];

Registry::get('view')->assign('company_id', $cid);

if ($mode == 'offices') {

        Registry::get('view')->assign('mode', 'view');
        $offices = fn_agents_get_company_offices($cid);
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
        $errors = fn_agents_get_office_fields_errors($_REQUEST['office']);
        if (empty($errors)) {
            $query = db_process("INSERT INTO ?:company_offices ?e", array($_REQUEST['office']) );
            db_query( $query);
            fn_set_notification('N', fn_get_lang_var('information'),  fn_get_lang_var('text_office_is_created') );
            $redirect_url = fn_url('agents.offices') . "&company_id=$cid";
        } else {
            fn_agents_display_errors($errors);
        }
    }
    Registry::get('view')->assign('content_tpl', 'views/agents/offices.tpl');
    return array(CONTROLLER_STATUS_OK, $redirect_url);

}
elseif ($mode == 'office_shippings') {
    $oid = $_REQUEST['office_id'];
    if (empty($oid)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

}

