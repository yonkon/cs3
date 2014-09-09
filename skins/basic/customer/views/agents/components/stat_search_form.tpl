{* $Id:	search_form.tpl	0 2006-07-28 19:49:30Z	seva $	*}

{capture name="section"}

<form action="{""|fn_url}" name="general_stats_search_form" method="get">

{include file="common_templates/period_selector.tpl" period=$statistic_search.period form_name="general_stats_search_form" tim_from=$statistic_search.start_date time_to=$statistic_search.end_date}

{*<div class="form-field">*}
	{*<label>{$lang.action}:</label>*}
	{*{html_checkboxes options=$payout_options name="statistic_search[payout_id]" selected=$statistic_search.payout_id columns=4}*}
{*</div>*}

<div class="form-field">
	<label>{$lang.amount} ({$currencies.$primary_currency.symbol}):</label>
	<input type="text" name="statistic_search[amount_from]" value="{$statistic_search.amount_from}" size="6" class="input-text-short" />&nbsp;-&nbsp;<input type="text" name="statistic_search[amount_to]" value="{$statistic_search.amount_to}" size="6" class="input-text-short" />
</div>

{*<div class="form-field">*}
	{*<label>{$lang.payout_status}:</label>*}
	{*{html_checkboxes options=$status_options name="statistic_search[status]" selected=$statistic_search.status columns=3}*}
{*</div>*}

    <select name="order_status">
        <option value="">{$lang.order_status}</option>
        {foreach from=$order_statuses item="st"}
            <option value="{$st.status}" {if $st.status == $order_status}selected="selected"{/if}>{$st.description}</option>
        {/foreach}
    </select>

    <div class="form-field period-select-date calendar" >
        <label>{$lang.paid_date}</label>
        {include file="common_templates/calendar.tpl" date_id="paid_date_from" date_name="paid_date_from" date_val=$search.paid_date_from start_year=$settings.Company.company_start_year}
        <span class="period-dash">&#8211;</span>
        {include file="common_templates/calendar.tpl" date_id="paid_date_to" date_name="paid_date_to" date_val=$search.paid_date_to start_year=$settings.Company.company_start_year}
    </div>
    <div class="form-field">
        <select class="clr" name="company_id">
            <option value="" {if empty($product_id)}selected="selected"{/if}>{$lang.company}</option>
            {foreach from=$companies item="com"}
                <option value="{$com.company_id}" {if $com.company_id == $company_id}selected="selected"{/if}>{$com.company}</option>
            {/foreach}
        </select>

        <select class="clr" name="product_id">
            <option value="" {if empty($product_id)}selected="selected"{/if}>{$lang.product}</option>
            {foreach from=$products item="pr"}
                <option value="{$pr.product_id}" {if $pr.product_id == $product_id}selected="selected"{/if}>{$pr.product}</option>
            {/foreach}
        </select>
     </div>


<div class="buttons-container">{include file="buttons/button.tpl" but_text=$lang.search but_name="dispatch[$controller.$mode/search]"}</div>
</form>


{/capture}
{include file="common_templates/section.tpl" section_title=$lang.search section_content=$smarty.capture.section}
