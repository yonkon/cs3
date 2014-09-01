<div class="agents-filters">
    <form method="post" id="filters">
    <input type="hidden" name="dispatch" value="agents.orders">
    <input type="hidden" name="page" id="page" value="{$pagination.page|default:1}">

        <select style="width: 250px;margin: 8px;" id="company" name="where[company_id]">
        <option value="">- {$lang.select_company} -</option>
        {foreach from=$companies item="company" key="code"}
            <option {if !empty($where.company_id) && $company.company_id == $where.company_id}selected="selected"{/if}  value="{$company.company_id}">{$company.company}</option>
        {/foreach}
    </select>
    <select style="width: 250px;margin: 8px;" id="product" name="where[product_id]">
        <option value="">- {$lang.select_product} -</option>
        {foreach from=$products item="product" key="code"}
            <option {if !empty($where.product_id) && $product.product_id == $where.product_id}selected="selected"{/if}  value="{$product.product_id}">{$product.product}</option>
        {/foreach}
    </select>
<br>
        {$lang.Sort_by_name} <select style="width: 70px;" name="order[name]">
            <option></option>
            <option {if !empty($order.name) && $order.name == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($order.name) && $order.name == 'desc'}selected="selected" {/if}>desc</option>
        </select>

        {$lang.price} <select style="width: 70px;" name="order[price]">
            <option></option>
            <option {if !empty($order.price) && $order.price == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($order.price) && $order.price == 'desc'}selected="selected" {/if}>desc</option>
        </select>
        {*{$lang.profit} <select style="width: 70px;" name="order[profit]">*}
            {*<option></option>*}
            {*<option {if !empty($order.profit) && $order.profit == 'asc'}selected="selected" {/if}>asc</option>*}
            {*<option {if !empty($order.profit) && $order.profit == 'desc'}selected="selected" {/if}>desc</option>*}
        {*</select>*}
        {$lang.City} <select style="width: 150px;margin: 8px;" name="filter_city" id="city">
            <option value="">- {$lang.select_city} -</option>
            {foreach from=$all_cities item="city" key="code"}
                <option {if !empty($client.city) && $city.city_id == $client.city}selected="selected"{/if}  value="{$city.city_id}">{$city.city}</option>
            {/foreach}
        </select>
        <br>
        {$lang.Status} <select style="width: 150px;" name="where[status]">
            <option value="">{$lang.Status}</option>
            {foreach from=$order_statuses item="status" key="code" }
                <option value="{$status.status}" {if !empty($where.status) && $status.status == $where.status}selected="selected"{/if}  >{$status.description}</option>
            {/foreach}
        </select>
        <button class="button green"  type="submit" value="{$lang.apply_filter}">{$lang.apply_filter}</button>
        </form>
    </div>


<div id="orders_div" class="clr">
    {foreach from=$orders item="order"}
        <p class="padding-top-2em">{$lang.Order} {$order.order_id} </p>
    <form>
        <input type="hidden" name="order_id" value="{$order.order_id}">
        <input type="hidden" name="dispatch" value="agents.order_make">
    <div class="order_div">
        <table class="table">
            <tr>
                <td style="width: 100px">
                    <a href="{"agents.product_info"|fn_url}&product_id={$order.product_id}">
                        <img class="product-image" src="{$order.product_data.image.image_path|unescape|fn_generate_thumbnail:$settings.Thumbnails.product_lists_thumbnail_width:$settings.Thumbnails.product_lists_thumbnail_height:true|escape}">
                    </a>
                </td>
                <td style="width: 300px" colspan="2">
                    <h2><a href="{"agents.product_info"|fn_url}&product_id={$order.product_id}">{$order.product_data.product}</a></h2>
                    <div class="product-description">{$order.product_data.description|unescape|truncate:360}</div>
                </td>
                <td>
                    <span id="item_{$order.product_data.product_id}_count_text" class="price">{$order.product_data.base_price|floatval|format_price:$currencies.$secondary_currency:'price':"price big":true}</span>
                    {if $mode == 'orders_saved'}
                        <div>
                            <button class="green button" type="submit" name="submit" value="submit">{$lang.checkout}</button>
                        </div>
                    {/if}
                    <div class="shipping">{if true || $order.product_data.free_shipping || $order.product_data.edp_shipping || $order.product_data.shipping_freight}<img class="shipping-img" src="/skins/basic/customer/views/agents/images/shipping.png">{/if}
                    </div>
                </td>
            </tr>
            <tr>
                <td>{if $order.company_data.image_path}
                    <a href="{"agents.company_info"|fn_url}&product_id={$order.product_id}">
                        <img src="{$order.company_data.image_path|unescape|fn_generate_thumbnail:$settings.Thumbnails.product_lists_thumbnail_width:$settings.Thumbnails.product_lists_thumbnail_height:true|escape}">
                    </a>{/if}
                </td>
                <td colspan="2"><div>{$order.company_data.company_description|default|unescape|truncate:360}</div></td>
                <td>
                    {*<span>{$lang.profit}: {$order.product_data.profit|floatval|format_price:$currencies.$secondary_currency:'price':"price big":true}</span><br/>*}
                    <span class="status">{$lang.status}: {$order.status_description}</span>
                </td>
            </tr>
            {if $mode != 'orders_saved'}
            <tr>
                <td>
                    <p class="underlined">{$lang.FIO_client}: {$order.b_lastname} {$order.b_firstname} {$order.b_midname}</p>
                    <p class="underlined">{$lang.mail}: {$order.b_email}<br/> {$order.b_phone}</p>
                    <p class="underlined">{$lang.registration_date}: {$order.registration_date|date_format}</p>
                </td>
                <td colspan="2">
                    {$lang.Comment}:
                    <p class="comment" id="comment_{$order.order_id}">
                        {$order.comment}
                    </p>
                    {*<p class="bold">*}
                        {*{$lang.Add_comment}*}
                    {*</p>*}
                    {*<input type="text" name="comment">*}
                </td>
                <td>
                    &nbsp;
                    {*<button class="green button" type="button" name="send" value="send">{$lang.Send}</button>*}
                </td>
            </tr>
            {/if}
        </table>
    </div>
</form>
{/foreach}
    {include file="views/agents/pagination.tpl"}

</div>

{literal}
<script type="text/javascript">
    {/literal}
    var url_products = '{'agents.ajax_get_products'|fn_url}';
    var url_cities = '{'agents.ajax_get_cities'|fn_url}';
    var url_offices = '{'agents.ajax_get_offices'|fn_url}';
    {literal}

    $('#company').change(function() {
        var company_id = $('#company').val();
        var data = {
            company_id : company_id
        };
        ajax_get_options(url_products, data, '#product');
        ajax_get_options(url_cities, data, '#city');
        ajax_get_options(url_offices, data, '#office');
    });
    $('#city').change(function() {
        var company_id = $('#company').val();
        var data = {
            company_id : company_id,
            city_id : $(this).val()
        };
        ajax_get_options(url_products, data, '#product');
        ajax_get_options(url_offices, data, '#office');
    })

</script>
{/literal}