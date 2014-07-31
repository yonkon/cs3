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
            <tr id="client_row_{$client.profile_id}">
                <td>{$client.profile_name}</td>
                <td>{$client.b_phone}</td>
                <td>{$client.b_email}</td>
                <td>{$client.registration_date}</td>
                <td>{$client.comment}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
