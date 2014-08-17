<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/


if ( !defined('AREA') ) { die('Access denied'); }

//
// Forbid posts to index script
//
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	return array(CONTROLLER_STATUS_NO_PAGE);
}

if ($mode == 'index') {
    $images = fn_agents_get_company_logos();
    $total_agents = fn_agents_get_total_agents_numbers();
    Registry::get('view')->assign('company_slider', array('images' => $images));
    Registry::get('view')->assign('total_agents', $total_agents);
    Registry::get('view')->assign('total_agents_use_images', false);
}



?>