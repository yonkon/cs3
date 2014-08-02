<?php



if (!defined('AREA')) { die('Access denied'); }

if (empty($auth['user_id'])) {
    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form");
}

if (!empty($auth['user_type']) && $auth['user_type'] != 'P' || empty($auth['user_type'])) {
    return array(CONTROLLER_STATUS_DENIED);
}


if ($mode == 'success_add') {

    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "profiles.add");
    }

    fn_add_breadcrumb(fn_get_lang_var('registration'));
}

elseif ($mode == 'add_ticket') {
    if (!empty($_REQUEST['submit'])) {
        $ticket = $_REQUEST;
        $ticket['user_id'] = $auth['user_id'];
        $errors = fn_support_get_ticket_fields_errors($ticket);
        if (empty($errors) ) {
            $user_email = db_get_field(db_process('SELECT email FROM ?:users where user_id = ?i', array($auth['user_id'])));





            $uploaddir = DIR_CUSTOM_FILES;
            $uploadfile = $uploaddir . basename($_FILES['application']['name']);


            if (move_uploaded_file($_FILES['application']['tmp_name'], $uploadfile)) {
                $ticket["file_path"] = $uploadfile;
            } else {

                fn_set_notification('E', fn_get_lang_var('error'), fn_get_lang_var('file_not_uploaded'));
            }









                	$from_email = Registry::get('settings.Company.company_users_department');
            $ticket_id = fn_support_create_ticket($ticket);
            Registry::get('view_mail')->assign('theme', $_REQUEST['theme']);
            Registry::get('view_mail')->assign('message', $_REQUEST['message']);
            Registry::get('view_mail')->assign('file_path', $_REQUEST['file_path']);
            $attachments = array();
            $fb_files = fn_filter_uploaded_data('fb_files');

            if (!empty($fb_files)) {
                foreach ($fb_files as $k => $v) {
                    $attachments[$v['name']] = $v['path'];
                    $form_values[$k] = $v['name'];
                }
            }
           fn_send_mail($from_email, $user_email,'support/new_ticket_subj.tpl', 'support/new_ticket_message.tpl', $_REQUEST['file'], $u_data['lang_code']);
        } else {
            foreach ($errors as $field=>$field_errors) {
                foreach($field_errors as $error) {
                    $error_text = fn_get_lang_var('field') . ' "' . fn_get_lang_var($field) . '" ' . fn_get_lang_var($error);
                    fn_set_notification('E', fn_get_lang_var('error'), $error_text);
                }
            }
        }
        if(!empty($ticket_id)) {
            fn_set_notification('N', fn_get_lang_var('notice'), fn_get_lang_var('support_ticket_created'));
        }
    }
    Registry::get('view')->assign('content_tpl', 'views/support/add_ticket.tpl');
    return array(CONTROLLER_STATUS_OK);
}


    function fn_get_agent_by_id($id) {
        $agent = db_get_row('SELECT * FROM ?:users WHERE user_id = ?i', $id );

        return $agent;
    }

function fn_support_get_ticket_fields_errors($ticket) {
    $not_empty_fields = array(
        'theme',
        'message',
        'question_type'
    );

    $integer_fields = array(
        'user_id'
    );
    $errors = array();

    foreach ($not_empty_fields as $not_empty) {
        if(empty($ticket[$not_empty]) ) {
            $errors[$not_empty][] = 'is_empty';
        }
    }


    foreach ($integer_fields as $numeric) {
        if (!is_numeric($ticket[$numeric])) {
            $errors[$numeric][] = 'invalid_value';
        }
    }

    return $errors;
}

function fn_support_create_ticket ($ticket) {

    $ticket_id = db_query("INSERT INTO ?:support_tickets ?e", $ticket);


    return $ticket_id;

}
