<?php


if ( !defined('AREA') )	{ die('Access denied');	}

if (empty($auth['user_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
}

if (!empty($auth['user_type']) && $auth['user_type'] != 'P' || empty($auth['user_type'])) {
    return array(CONTROLLER_STATUS_DENIED);
}

include dirname(__FILE__) . "/fn_agents.php";


if ($mode == 'offices') {
    Registry::get('view')->assign('mode', 'offices');
    Registry::get('view')->assign('content_tpl', 'views/agents/offices.tpl');
    return array(CONTROLLER_STATUS_OK);
}