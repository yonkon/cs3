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


if (!defined('AREA') ) { die('Access denied'); }

if ($mode == 'list') {

	fn_add_breadcrumb(fn_get_lang_var('promotions'));

	$params = array (
		'active' => true,
		/*'zone' => 'catalog',*/
		'get_hidden' => false,
	);

	list($promotions) = fn_get_promotions($params);

	$view->assign('promotions', $promotions);
}

?>