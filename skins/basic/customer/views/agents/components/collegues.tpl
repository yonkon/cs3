<h1 class="h1 center">{$lang.collegues}</h1>
<form method="post" action="{'agents.add_subagent'|fn_url}">
    <input type="hidden" name="dispatch" value="agents.add_collegue_form">
    <button class="button big green float-right center" type="submit" value="submit">{$lang.Register_subagent}</button>
</form>
<div id="agents_collegues_div">
    <table id="agents_collegues_table">
        <thead>
        <tr>
            <th style="width: 30%">{$lang.FIO}</th>
            <th style="width: 15%">{$lang.Phone}</th>
            <th style="width: 20%">{$lang.Mail}</th>
            <th style="width: 15%">{$lang.Registration_date}</th>
            <th style="width: 20%">{$lang.Comment}</th>
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
