<h1 class="h1 center">{$lang.Clients}</h1>
<form method="post" action="{'agents.add_client_form'|fn_url}">
    <input type="hidden" name="dispatch" value="agents.add_client_form">
    <button class="button big green float-right center"  type="submit" value="{$lang.Add_new_client}">{$lang.Add_new_client}</button>
</form>
<div id="agents_clients_div">
    <div id="message_success" class="notification-n hidden"><p style="margin-left: 10%;">{$lang.Update_successfull}</p></div>
    <div id="message_error" class="notification-e hidden"><p style="margin-left: 10%;">{$lang.Update_failed}</p></div>
    <table id="agents_clients_table">
        <thead>
        <tr>
            <th>{$lang.FIO}</th>
            <th>{$lang.Phone}</th>
            <th>{$lang.Mail}</th>
            <th>{$lang.Registration_date}</th>
            <th>{$lang.Comment}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$clients item="client"}
            <tr id="client_row_{$client.profile_id}" data-id="{$client.profile_id}">
                <td><input type="text" value="{$client.profile_name}" name="profile_name" class="ajax_input"> </td>
                <td><input type="text" value="{$client.b_phone}"name="b_phone" class="ajax_input"> </td>
                <td><input type="text" value="{$client.b_email}" name="b_email" class="ajax_input"> </td>
                <td>{$client.registration_date|date_format:"`$settings.Appearance.date_format`"}</td>
                <td><textarea name="comment" class="ajax_input">{$client.comment}</textarea></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    {include file="views/agents/pagination.tpl"}
</div>



{literal}
<script type="text/javascript">
$(document).ready(function() {
    var $message_success = $('#message_success');
    var $message_error = $('#message_error');
    $('.ajax_input').each(function(i,el) {
        $(el).blur(function(){
            var $this = $(this);

            $.ajax({
                type: "POST",
                url: {/literal}"{'agents.update_client'|fn_url}"{literal},
                data: { profile_id: $this.parent().parent().data('id'), field: $this.attr("name"), value: $this.val() }
            })

            .success(function( msg ) {
                if(typeof msg != 'undefined') {
                    var data = JSON.parse(msg);
                    if(typeof data.status != 'undefined' && data.status == 'OK') {
                        agents_popup($message_success, 'fast', 4000);
                    } else {
                        agents_popup($message_error, 'fast', 4000);
                    }
                }
            })

            .error(function( msg ) {
                agents_popup($message_error, 'fast', 4000);
            });

        });
    });
});
function agents_popup(element, show, hide) {
    element.show(show, function() {
        setTimeout(function(){
            element.hide();
        }, hide)
    });
}
    {/literal}

</script>
