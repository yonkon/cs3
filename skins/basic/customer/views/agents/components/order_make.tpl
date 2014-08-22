<div id="order_make_div">
    <div id="order_make_top">
        <h2 class="lightbox-header-text h2">{$lang.New_order_Main_info}</h2>
        {*<img src="/images/close.png" class="close" alt="{$lang.close}">*}
        <p class="graytext">{$lang.Fill_client_data_please}</p>
    </div>
    <div id="order_make_content">
        <form method="post">
        <input type="hidden" name="step" value="{$step}">
        <input type="hidden" name="client[affiliate_id]" value="{$auth.user_id}">
        <input type="hidden" name="item_count" value="{$product.amount|default:1}">
        <div>
            <label class="client_filds_add_product_label" for="client_fio">{$lang.FIO}</label>
            <input class="client_filds_add_product" id="client_fio" name="client[fio]" value="{$client.fio }">
        </div>
        <div>
            <label class="client_filds_add_product_label" for="client_phone">{$lang.Phone}</label>
            <input class="client_filds_add_product" id="client_phone" name="client[phone]" value="{$client.phone }">
        </div>
        <div>
            <span>{$lang.Contact_phone_number_for_order_approvement}</span>
        </div>
        <div>
            <label class="client_filds_add_product_label" for="client_company">{$lang.Company}</label>
            <select class="client_filds_add_product" id="client_company" name="client[company]">
                <option value="">- {$lang.select_company} -</option>
                {foreach from=$companies item="company" key="code"}
                    <option {if $company.company_id == $client.company}selected="selected"{/if}  value="{$company.company_id}">{$company.company}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <label class="client_filds_add_product_label" for="client_region">{$lang.Region}</label>
            <option value="">- {$lang.select_region} -</option>
            <select class="client_filds_add_product" id="client_region" name="client[region]">
                <option value="">- {$lang.select_region} -</option>
                {foreach from=$regions item="region"  key="code"}
                    <option {if $region.RegionID == $client.region} selected="selected"{/if} value="{$region.RegionID}">{$region.name}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <label class="client_filds_add_product_label" for="client_city">{$lang.City}</label>
            <select class="client_filds_add_product" id="client_city" name="client[city]">
                <option value="">- {$lang.select_city} -</option>
                {foreach from=$cities item="city"  key="code"}
                    <option {if $city.CityId == $client.city} selected="selected"{/if} value="{$city.CityId}">{$city.name}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <label class="client_filds_add_product_label" for="client_office">{$lang.Office}</label>
            <select class="client_filds_add_product" id="client_office" name="client[office]">
                <option value="">- {$lang.select_office} -</option>
                {foreach from=$offices item="office"  key="code"}
                    <option {if $office.office_id == $client.office} selected="selected"{/if} value="{$office.office_id}">{$office.office_name}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <label for="client_need_shipment">{$lang.Need_shipment}</label>
            <input type="checkbox" id="client_need_shipment" name="client[need_shipment]" {if $client.need_shipment}checked="checked" {/if}>
        </div>
        <div>
            <label class="client_filds_add_product_label" for="client_comment">{$lang.Comment}</label>
            <textarea class="client_filds_add_product_area" id="client_comment" name="client[comment]">{$client.comment }</textarea>
        </div>
        <div>
                <span class="graytext">{$lang.Order_comment_help_text}</span>
        </div>
        <div>
            <input type="checkbox" id="client_notify" name="client[notify]" {if $client.notify}checked="checked" {/if}>
            <label class="client_filds_add_product_label_mail" for="client_notify">{$lang.Notify_user_by_mail}</label>
            <input class="client_filds_add_product" id="client_email" name="client[email]" value="{$client.email}">
        </div>
    </div>
    <div id="order_make_bottom">
        <button id="order_make_submit" class="button big green center-block" style="
margin-top: 5%;" type="submit">{$lang.Next}</button>
    </div>
    </form>
</div>

<script type="text/javascript">
    var url_regions = '{'agents.ajax_get_regions'|fn_url}';
    var url_cities = '{'agents.ajax_get_cities'|fn_url}';
    var url_offices = '{'agents.ajax_get_offices'|fn_url}';
    var url_save = '{'agents.ajax_save_product'|fn_url}';

    {literal}
    $('#client_company').change(function() {
        var data = {
            is_options :true,
            company_id : $(this).val()
        };
        ajax_get_options(url_regions, data, '#client_region');
        ajax_get_options(url_cities, data, '#client_city');
        ajax_get_options(url_offices, data, '#client_office');
    });
    $('#client_region').change(function() {
        var company_id = $('#client_company').val();
        var data = {
            is_options :true,
            company_id : company_id,
            region_id : $(this).val()
        };
        ajax_get_options(url_cities, data, '#client_city');
        ajax_get_options(url_offices, data, '#client_office');
    });
    $('#client_city').change(function() {
        var company_id = $('#client_company').val();
        var data = {
            is_options :true,
            company_id : company_id,
            city_id : $(this).val()
        };
        ajax_get_options(url_offices, data, '#client_office');
    });
</script>
{/literal}
