{include file="views/agents/components/stat_search_form.tpl"}
<p><a onclick="$('#general_statistics').toggle(); return false;"><strong>{$lang.general_statistics} &#187;</strong></a></p>
<div id="general_statistics" {*class="hidden"*}>
    {assign var="report_type" value="all"}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" class="table">
        <tr>
            {if $is_vendor == false}
                <th width="40%">{$lang.profit_source}</th>
            {/if}
            <th class="right" width="20%">{$lang.orders_paid_count}</th>
            {if $is_vendor}<th class="right" width="20%">{$lang.total_profit}</th>
            {else}<th class="right" width="20%">{$lang.admin_report_agent_total_profit}</th>
            {/if}
            {if $is_vendor == false}
                <th class="right" width="20%">{$lang.admin_pure_site_profit}</th>
            {/if}
            {if $is_vendor}<th class="right" width="20%">{$lang.site_profit}</th>
            {else}<th class="right" width="20%">{$lang.admin_site_profit}</th>
        {/if}
        </tr>
        {if $general_stats|count}
            {foreach from=$general_stats item='g_st' key="g_st_lang_var"}
                <tr {cycle values=" ,class=\"table-row\""}>
                    {if $is_vendor == false && $report_type == 'all'}
                        <td><strong>{$g_st.action}</strong></td>
                    {/if}
                    <td class="right">{$g_st.count|default:"0"}</td>
                    <td class="right">{include file="common_templates/price.tpl" value=$g_st.sum|round:2}</td>
                    {if $is_vendor == false}
                        {math equation="pr - su" pr=$g_st.site_profit su=$g_st.sum assign="pure_site_profit"}
                        <td class="right">{include file="common_templates/price.tpl" value=$pure_site_profit|round:2}</td>
                    {/if}
                    <td class="right">{include file="common_templates/price.tpl" value=$g_st.site_profit|round:2}</td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan={if $report_type == 'all'}"4"{else}"3"{/if}><p class="no-items">{$lang.no_data_found}</p></td>
            </tr>
        {/if}
        <tr class="table-footer">
            <td colspan={if $report_type == 'all'}"4"{else}"3"{/if}>&nbsp;</td>
        </tr>
    </table>


</div>
{*{capture name="agents_extra_content"}*}

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
    <button class="green button" style="background-image: url(http://www.garantpostach.com.ua/images/Excel.gif); padding-left: 30px; background-color: #eee; background-repeat: no-repeat;" type="button" onclick="exportReport();">{$lang.export}</button>
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
            {if $is_vendor == false}
            <th>
                <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=company&amp;sort_order={$sort_order}" rev="pagination_contents">
                    {$lang.company}
                </a>{if $sort_by == "action"}{$sort_sign}{/if}
            </th>
            {/if}
            <th>
                <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=product&amp;sort_order={$sort_order}" rev="pagination_contents">
                    {$lang.product}
                </a>{if $sort_by == "action"}{$sort_sign}{/if}
            </th>
            {if ($is_vendor == false && ($report_type == 'agent' || $report_type == 'all'))}
                <th>
                    <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=partner&amp;sort_order={$sort_order}&amp;post_sort_by=agent" rev="pagination_contents">
                        {$lang.agent}
                    </a>{if $sort_by == "action"}{$sort_sign}{/if}
                </th>
            {/if}
            {if $is_vendor == false && ($report_type == 'subagent' || $report_type == 'all')}
                <th>
                    <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=partner&amp;sort_order={$sort_order}&amp;post_sort_by=subagent" rev="pagination_contents">
                        {$lang.subagent}
                    </a>{if $sort_by == "action"}{$sort_sign}{/if}
                </th>
            {/if}
            <th>
                <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;post_sort_by=sum&amp;sort_order={$sort_order}" rev="pagination_contents">
                    {$lang.price}
                </a>{if $sort_by == "action"}{$sort_sign}{/if}
            </th>
            {if $is_vendor == false && ($report_type == 'agent' || $report_type == 'all')}
                <th>
                    <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=cost&amp;post_sort_by=agent_profit&amp;sort_order={$sort_order}" rev="pagination_contents">
                        {$lang.agent_profit}
                    </a>{if $sort_by == "action"}{$sort_sign}{/if}
                </th>
            {/if}
            {if $is_vendor == false && ($report_type == 'subagent' || $report_type == 'all')}
                <th>
                    <a class="{$ajax_class}" href="{$url_prefix}{$c_url}&amp;sort_by=cost&amp;post_sort_by=subagent_profit&amp;sort_order={$sort_order}" rev="pagination_contents">
                        {$lang.agent_profit_from_subagent}
                    </a>{if $sort_by == "action"}{$sort_sign}{/if}
                </th>
            {/if}
            <th>
                {if $is_vendor}{$lang.site_profit}
                    {else}{$lang.admin_site_profit}
                {/if}
            </th>
            {if $is_vendor == false}
            <th>{$lang.admin_pure_site_profit}</th>
            {/if}
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
                    <a href="{'agents.orders'|fn_url}&where[order_id]={$row_stats.order.order_id}&where[user_id]={$row_stats.customer_id}"> {$row_stats.order.order_id}</a>
                </td>
                {if $is_vendor == false}
                <td>
                    {$row_stats.order.company_data.company}
                    {if !empty($row_stats.order.company_data.company_contract_id)}
                        <br>
                        <span class="small-note">{$lang.company_contract_id}: {$row_stats.order.company_data.company_contract_id}</span>
                    {/if}
                </td>
                {/if}
                <td>
                    {$row_stats.order.product_data.product}
                </td>
                {if ($is_vendor == false && ($report_type == 'agent' || $report_type == 'all'))}
                    <td>
                        {$row_stats.partner_lastname} {$row_stats.partner_firstname}
                        &nbsp;
                    </td>
                {/if}
                {if $is_vendor == false && ($report_type == 'subagent' || $report_type == 'all')}
                    <td>
                        {if $is_vendor == false && $row_stats.partner_id != $row_stats.customer_id}
                            {$row_stats.customer_lastname} {$row_stats.customer_firstname}
                        {/if}
                        &nbsp;
                    </td>
                {/if}
                <td>
                    {include file="common_templates/price.tpl" value=$row_stats.order.total|round:2}
                </td>
                {if $is_vendor == false && ($report_type == 'agent' || $report_type == 'all')}
                    <td>
                        {if $row_stats.partner_id == $row_stats.customer_id}
                            {include file="common_templates/price.tpl" value=$row_stats.amount|round:2}
                        {/if}
                    </td>
                {/if}
                {if $is_vendor == false && ($report_type == 'subagent' || $report_type == 'all')}
                    <td>
                        {if $row_stats.partner_id != $row_stats.customer_id}
                            {include file="common_templates/price.tpl" value=$row_stats.amount|round:2}
                        {/if}
                    </td>
                {/if}
                <td>
                    {include file="common_templates/price.tpl" value=$row_stats.site_profit|round:2}
                </td>
                {if $is_vendor == false}
                    <td>
                        {include file="common_templates/price.tpl" value=$row_stats.pure_site_profit|round:2}
                    </td>
                {/if}
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
                <td colspan={if $report_type == 'all'}"11"{else}"9"{/if}><p class="no-items">{$lang.no_data_found}</p></td>
            </tr>
        {/foreach}
        <tr class="table-footer">
            <td colspan={if $report_type == 'all'}"11"{else}"9"{/if}>&nbsp;</td>
        </tr>
    </table>
    {include file="common_templates/pagination.tpl"}
{*{/capture}*}
{capture name="mainbox_title"}{$lang.commissions}{/capture}