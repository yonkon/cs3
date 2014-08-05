<h1 class="center">{$lang.collegues}</h1>
<form method="post" action="{'agents.add_subagent'|fn_url}">
    <input type="hidden" name="dispatch" value="agents.add_collegue_form">
    <button class="right" type="submit" value="submit">{$lang.Register_subagent}</button>
</form>
<div id="agents_collegues_div">
    <table id="agents_collegues_table">
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
        {foreach from=$collegues item="collegue"}
            <tr id="collegue_row_{$collegue.user_id}">
                <td>{$collegue.lastname} {$collegue.firstname} {$collegue.midname}</td>
                <td>{$collegue.phone}</td>
                <td>{$collegue.email}</td>
                <td>{$collegue.registration_date|date_format:"`$settings.Appearance.date_format`"}</td>
                <td>{$collegue.comment}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
