<div class="agents-filters">
    <form method="post" id="filters">
    <input type="hidden" name="dispatch" value="agents.orders_saved">
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
        {*{$lang.City} <select style="width: 150px;margin: 8px;" name="filter_city" id="city">
            <option value="">- {$lang.select_city} -</option>
            {foreach from=$all_cities item="city" key="code"}
                <option {if !empty($client.city) && $city.city_id == $client.city}selected="selected"{/if}  value="{$city.city_id}">{$city.city}</option>
            {/foreach}
        </select>*}
        <br>

        <button class="button green"  type="submit" value="{$lang.apply_filter}">{$lang.apply_filter}</button>
        </form>
    </div>


<div id="orders_div" class="clr">
    {foreach from=$products item="product"}
    <form id="order_div_{$product.product_id}">
        <input type="hidden" name="dispatch" value="agents.order_make">
        <input type="hidden" name="product_id" value="{$product.product_id}">
    <div class="order_div">
        <table>
            <tr>
                <td style="width: 100px">
                    <a href="{"agents.product_info"|fn_url}&product_id={$product.product_id}">
                        <img class="product-image" src="{$product.image.image_path|unescape|fn_generate_thumbnail:$settings.Thumbnails.product_lists_thumbnail_width:$settings.Thumbnails.product_lists_thumbnail_height:true|escape}">
                    </a>
                </td>
                <td style="width: 300px" colspan="2">
                    <h2><a href="{"agents.product_info"|fn_url}&product_id={$product.product_id}">{$product.product}</a></h2>
                    <div class="product-description">{$product.description|unescape|truncate:360}</div>
                </td>
                <td>
                    <span id="item_{$product.product_id}_count_text" class="price">{$product.price|floatval|format_price:$currencies.$secondary_currency:'price':"price big":true}</span>
                        <div>
                            <button class="green button w80" type="submit" name="submit" value="submit">{$lang.checkout}</button>
                            <button class="green button w80 margin-top" type="button" name="remove" value="remove" onclick="removeSavedOrder({$product.product_id});">{$lang.remove}</button>
                        </div>
                </td>
            </tr>
            <tr>
                <td>{if $product.company.image_path}
                    <a href="{"agents.company_info"|fn_url}&product_id={$product.product_id}">
                        <img src="{$product.company.image_path|unescape|fn_generate_thumbnail:$settings.Thumbnails.product_lists_thumbnail_width:$settings.Thumbnails.product_lists_thumbnail_height:true|escape}">
                    </a>{/if}
                </td>
                <td colspan="2"><div>{$product.company.company_description|default|unescape|truncate:360}</div></td>
                <td>
                    <span>{$lang.profit}: {$product.profit|floatval|format_price:$currencies.$secondary_currency:'price':"price big":true}</span><br/>
                </td>
            </tr>

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
    var url_remove = '{'agents.ajax_remove_saved_order'|fn_url}';
    {*var url_offices = '{'agents.ajax_get_offices'|fn_url}';*}

    {literal}

    $('#company').change(function() {
        var company_id = $('#company').val();
        var data = {
            company_id : company_id
        };
        ajax_get_options(url_products, data, '#product');
//        ajax_get_options(url_cities, data, '#city');
//        ajax_get_options(url_offices, data, '#office');
    });
//    $('#city').change(function() {
//        var company_id = $('#company').val();
//        var data = {
//            company_id : company_id,
//            city_id : $(this).val()
//        };
//        ajax_get_options(url_products, data, '#product');
////        ajax_get_options(url_offices, data, '#office');
//    })
function removeSavedOrder(id) {
    $.ajax({
        type: "POST",
        url: url_remove,
        data: {
            'product_id' : id
        }
    })

            .success(function( msg ) {
                if(typeof msg != 'undefined') {
                    var data = JSON.parse(msg);
                    if(typeof data.status != 'undefined' && data.status == 'OK') {
                        $('#order_div_'+id).remove();
                    } else {
                        alert('JSON malformat error');
                    }
                }
            })

            .error(function( msg ) {
                alert('Server error');
            });
}
</script>
{/literal}