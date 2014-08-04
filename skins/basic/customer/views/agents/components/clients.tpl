<h1 class="center">{$lang.Clients}</h1>
<form method="post" action="{'agents.add_client_form'|fn_url}">
    <input type="hidden" name="dispatch" value="agents.add_client_form">
    <button class="right" type="submit" value="{$lang.Add_new_client}">{$lang.Add_new_client}</button>
</form>
<div id="agents_clients_div">
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
</div>
<script type="text/javascript">
    {literal}
    $(document).ready(function() {
        $('.ajax_input').each(function(i,el) {
           $(el).blur(function(){
               var $this = $(this);
               $.ajax({
                   type: "POST",
                   url: {/literal}"{'agents.update_client'|fn_url}"{literal},
                   data: { profile_id: $this.parent().parent().data('id'), field: $this.attr("name"), value: $this.val() }
               })
               .success(function( msg ) {
                   alert( "Data Saved: " + msg );
               })
               .error(function( msg ) {
                   alert( "Data not Saved: " + msg );
               });

           });
        });
    });
    {/literal}

</script>