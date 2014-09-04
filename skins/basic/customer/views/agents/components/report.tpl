{include file="views/agents/components/stat_search_form.tpl"}
<p><a onclick="$('#general_statistics').toggle(); return false;"><strong>{$lang.general_statistics} &#187;</strong></a></p>
<div id="general_statistics" class="hidden">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" class="table">
        <tr>
            {*<th width="40%">{$lang.action}</th>*}
            <th class="right" width="20%">{$lang.orders_count}</th>
            <th class="right" width="20%">{$lang.agent_total_profit}</th>
            <th class="right" width="20%">{$lang.agent_average_profit}</th>
        </tr>
        {if $general_stats}
            {foreach from=$payout_types key="payout_id" item="a"}
                {assign var="payout" value=$general_stats.$payout_id}
                {assign var="payout_var" value=$a.title}
                {if $payout.count}
                    <tr {cycle values=" ,class=\"table-row\""}>
                        {*<td><strong>{$lang.$payout_var}</strong></td>*}
                        <td class="right">{$payout.count|default:"0"}</td>
                        <td class="right">{include file="common_templates/price.tpl" value=$payout.sum|round:2}</td>
                        <td class="right">{include file="common_templates/price.tpl" value=$payout.avg|round:2}</td>
                    </tr>
                {/if}
            {/foreach}
            {if false && $general_stats.total}
                {assign var="payout" value=$general_stats.total}
                <tr>
                    <td><strong>{$lang.total}</strong></td>
                    <td class="right"><strong>{$payout.count|default:"0"}</strong></td>
                    <td class="right"><strong>{include file="common_templates/price.tpl" value=$payout.sum|round:2}</strong></td>
                    <td class="right"><strong>{include file="common_templates/price.tpl" value=$payout.avg|round:2}</strong></td>
                </tr>
            {/if}
        {else}
            <tr>
                <td colspan="4"><p class="no-items">{$lang.no_data_found}</p></td>
            </tr>
        {/if}
        <tr class="table-footer">
            <td colspan="4">&nbsp;</td>
        </tr>
    </table>

    {*{if $additional_stats}*}
        {*<table cellpadding="2" cellspacing="1" border="0" class="margin-top">*}
            {*{foreach from=$additional_stats key="a_stats_name" item="a_stats_value"}*}
                {*<tr>*}
                    {*<td><strong>{$lang.$a_stats_name}</strong>:</td>*}
                    {*<td>{$a_stats_value}</td>*}
                {*</tr>*}
            {*{/foreach}*}
        {*</table>*}
    {*{/if}*}

</div>
{capture name="agents_extra_content"}

{include file="common_templates/pagination.tpl"}

{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}
{if $sort_order == "asc"}
    {assign var="sort_sign" value="&nbsp;&nbsp;&#8595;"}
{else}
    {assign var="sort_sign" value="&nbsp;&nbsp;&#8593;"}
{/if}
{if $settings.DHTML.customer_ajax_based_pagination == "Y"}
    {assign var="ajax_class" value="cm-ajax"}
{/if}
    <button class="green button" type="button" onclick="exportReport();">{$lang.export}</button>
{literal}
<script type="text/javascript">
    function exportReport() {
        var $form = $('form[name=general_stats_search_form]');
        var $submit = $('input[type=submit]', $form);
        $submit.attr('name', 'dispatch[agents.report_export/search]');
        $submit.click();
        return false;
    }
</script>
{/literal}
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table">
    <tr>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=order&amp;sort_order={$sort_order}" rev="pagination_contents">
                {$lang.order}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=company&amp;sort_order={$sort_order}" rev="pagination_contents">
            {$lang.company}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=product&amp;sort_order={$sort_order}" rev="pagination_contents">
            {$lang.product}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=partner&amp;sort_order={$sort_order}&amp;post_sort_by=agent" rev="pagination_contents">
            {$lang.agent}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=partner&amp;sort_order={$sort_order}&amp;post_sort_by=subagent" rev="pagination_contents">
            {$lang.subagent}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=sum&amp;sort_order={$sort_order}" rev="pagination_contents">
            {$lang.price}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=cost&amp;post_sort_by=agent_profit&amp;sort_order={$sort_order}" rev="pagination_contents">
            {$lang.agent_profit}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=cost&amp;post_sort_by=subagent_profit&amp;sort_order={$sort_order}" rev="pagination_contents">
            {$lang.agent_profit_from_subagent}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=status&amp;sort_order={$sort_order}" rev="pagination_contents">
                {$lang.status}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=date&amp;post_sort_by=0&amp;sort_order={$sort_order}" rev="pagination_contents">
            {$lang.registration_date}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
        <th>
            <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=paid_date&amp;sort_order={$sort_order}" rev="pagination_contents">
            {$lang.paid_date}
            </a>{if $sort_by == "action"}{$sort_sign}{/if}
        </th>
    </tr>
    {foreach from=$list_stats item="row_stats" name="commissions"}
        {cycle values=",table-row" assign="row_class_name"}
        {include file="addons/affiliate/views/aff_statistics/components/additional_data.tpl" data=$row_stats.data assign="additional_data"}
        <tr class="{$row_class_name}">
            <td>
                    {$row_stats.order.order_id}
            </td>
            <td>
                    {$row_stats.order.company_data.company}
            </td>
            <td>
                    {$row_stats.order.product_data.product}
            </td>
            <td>
                    {if $row_stats.partner_id == $row_stats.customer_id}
                        {$row_stats.customer_lastname} {$row_stats.customer_firstname}
                        {/if}&nbsp;
            </td>
            <td>
                {if $row_stats.partner_id != $row_stats.customer_id}
                    {$row_stats.customer_lastname} {$row_stats.customer_firstname}{/if}
                &nbsp;
            </td>
            <td>
                {$row_stats.order.total}
            </td>
            <td>
                {if $row_stats.partner_id == $row_stats.customer_id}
                    {$row_stats.amount}
                {/if}
            </td>
            <td>
                {if $row_stats.partner_id != $row_stats.customer_id}
                    {$row_stats.amount}
                {/if}
            </td>
            <td>
                {$row_stats.order.status_description}
            </td>
            <td>
                    {$row_stats.date|date_format}
            </td>
            <td>
                {if !empty($row_stats.payout_date)}
                 {$row_stats.payout_date|date_format}
                {/if}
                &nbsp;
            </td>
        </tr>
        {foreachelse}
        <tr>
            <td colspan="11"><p class="no-items">{$lang.no_data_found}</p></td>
        </tr>
    {/foreach}
    <tr class="table-footer">
        <td colspan="11">&nbsp;</td>
    </tr>
</table>
{include file="common_templates/pagination.tpl"}
{/capture}
{capture name="mainbox_title"}{$lang.commissions}{/capture}