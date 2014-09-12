<h1 class="h1 center">{$lang.collegues}</h1>
<a href="#" id="cycle_tabs_button" onclick="cycleTabs()">{$lang.to_see_collegues}</a>
<form method="post" action="{'agents.add_subagent'|fn_url}">
    <input type="hidden" name="dispatch" value="agents.add_collegue_form">
    <button class="button big green float-right center" type="submit" value="submit">{$lang.Register_subagent}</button>
</form>
<div id="agents_collegues_div">
    <div class="tab list {if $active_tab != 'list'}hidden{/if}">
        <table id="agents_collegues_table" class="table">
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
        {include file="views/agents/pagination.tpl"}
    </div>
    <div class="tab report {if $active_tab == 'list'}hidden{/if}">
        {include file="views/agents/components/report_collegues.tpl"}
    </div>
</div>
{*<div class="margin-top padding-1em">*}
{*</div>*}
{literal}
<script type="text/javascript">
    function cycleTabs() {
        {/literal}
        var list_lang = '{$lang.to_see_collegues}';
        var report_lang = '{$lang.to_see_report}';
        {literal}
        var this_btn = $('#cycle_tabs_button');
        if (this_btn.text() == list_lang) {
            this_btn.text(report_lang);
        } else {
            this_btn.text(list_lang);
        }
        $('.tab.list').toggleClass('hidden');
        $('.tab.report').toggleClass('hidden');
        $('.agents_extra_div').toggleClass('hidden');
    }
</script>
{/literal}