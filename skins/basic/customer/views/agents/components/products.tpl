<div class="agents-filters">
    <form method="post" id="filters">
    <input type="hidden" name="dispatch" value="agents.companies_and_products">
        <input type="hidden" id="page" name="page" value="{$pagination.page|default:1}">
        <select style="width: 250px;margin: 8px;" id="client_company" name="client[company]">
        <option value="">- {$lang.select_company} -</option>
        {foreach from=$companies item="company" key="code"}
            <option {if !empty($client.company) && $company.company_id == $client.company}selected="selected"{/if}  value="{$company.company_id}">{$company.company}</option>
        {/foreach}
    </select>
    <select style="width: 250px;" id="client_product" name="client[product]">
        <option value="">- {$lang.select_product} -</option>
        {foreach from=$all_products item="product" key="code"}
            <option {if !empty($client.product) && $product.product_id == $client.product}selected="selected"{/if}  value="{$product.product_id}">{$product.product}</option>
        {/foreach}
    </select>
<br>
        {$lang.Sort_by_name} <select style="width: 70px;" name="sort_name">
            <option></option>
            <option {if !empty($client.sort_name) && $client.sort_name == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($client.sort_name) && $client.sort_name == 'desc'}selected="selected" {/if}>desc</option>
        </select>

        {$lang.price} <select style="width: 70px;" name="sort_price">
            <option></option>
            <option {if !empty($client.sort_price) && $client.sort_price == 'asc'}selected="selected" {/if}>asc</option>
            <option{if !empty($client.sort_price) && $client.sort_price == 'desc'}selected="selected" {/if}>desc</option>
        </select>
        {$lang.profit} <select style="width: 70px;" name="sort_profit">
            <option></option>
            <option {if !empty($client.sort_profit) && $client.sort_profit == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($client.sort_profit) && $client.sort_profit == 'desc'}selected="selected" {/if}>desc</option>
        </select>
        {$lang.City} <select style="width: 150px;margin: 8px;" name="filter_city" id="client_city">
            <option value="">- {$lang.select_city} -</option>
            {foreach from=$all_cities item="city" key="code"}
                <option {if !empty($client.city) && $city.city_id == $client.city}selected="selected"{/if}  value="{$city.city_id}">{$city.city}</option>
            {/foreach}
        </select>
        <button
                class="green button"
                type="submit"
                value="filter"
                {literal}
                onclick="function(){$('#page').val('');}"
                {/literal}
                >{$lang.apply_filter}</button>
        </form>
    </div>
<div id="products_div">
    {foreach from=$products item="product"}
        <form id="form_{$product.product_id}">
            <input type="hidden" name="product_id" value="{$product.product_id}">
            <input type="hidden" name="dispatch" value="agents.order_make">
            <div class="product_div">
                <table>
                    <tr>
                        <td id="product_image"> <a href="{'agents.product_info'|fn_url}&product_id={$product.product_id}"><img class="product-image" src="/images/detailed/1/{$product.image.image_path}"></a></td>
                        <td id="product_name" colspan="2">
                            <h2><a href="{'agents.product_info'|fn_url}&product_id={$product.product_id}">{$product.product}</a></h2>
                            <div class="'product-description">{$product.full_description|unescape|truncate:360}</div>
                        </td>
                        <td id="product_buy">
                            <div class="product-count-buttons">
                                <a href="#" class="increase plus_minus" onclick="increase_count({$product.product_id}, 1, {$product.price});">+</a>
                                <a href="#" class="decrease plus_minus" onclick="increase_count({$product.product_id}, -1,{$product.price});">-</a>
                                <input type="hidden" name="item_count" id="item_{$product.product_id}_count" value='1' >
                            </div>
                            {assign var="price_id" value='price_'|cat:$product.product_id}
                            <span id="item_{$product.product_id}_count_text" class="price">{$product.price|floatval|format_price:$currencies.$secondary_currency:$price_id:"price big":true}</span>
                            <div>
                                <button id="order_submit_{$product.product_id}" class="big green button" type="submit" name="checkout" value="Оформить заявку">Оформить заявку</button>
                            </div>
                            <div class="shipping">{if true || $product.free_shipping || $product.edp_shipping || $product.shipping_freight}<img class="shipping-img" src="/skins/basic/customer/views/agents/components/shipping.png">{/if}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td id="company_img">{if $product.company.company_description}<a href="{'agents.company_info'|fn_url}&product_id={$product.product_id}"> <img class="company_image" src="{$product.company.image_path}"></a>{/if}</td>
                        <td id="company_desc" colspan="2"><div>{$product.company.company_description|default|unescape|truncate:360}</div></td>
                        <td id="add_to_save">
                            <span>{$lang.profit} {$product.profit|floatval|format_price:$currencies.$secondary_currency:$price_id:"profit":true}</span><br>
                            <button id="order_save_submit_{$product.product_id}"
                                    class="big green button"
                                    onclick="save_order({$product.product_id});">
                                Сохранить в кабинете
                            </button>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
    {/foreach}
</div>

{literal}
<script type="text/javascript">
    function increase_count(product_id, amount, price) {
        amount = parseInt(amount);
        price = parseInt(price);
        var $form = $('#form_'+product_id);
        var $input = $('#item_' + product_id + '_count');
        var $cost = $('#sec_price_' + product_id);
        var base_amount = $input.val();
        $input.val(parseInt(base_amount) + amount);
        var base_cost = parseInt($cost.text());
        $cost.text(base_cost + amount*price)
        event.preventDefault();
        return false;
    }

    function save_order(product_id) {
        var $form = $('#form_'+product_id);
        $('input[name=dispatch]', $form).val('agents.order_save');
        $form.submit();
    }

    {/literal}
    var url_products = '{'agents.ajax_get_products'|fn_url}';
    var url_cities = '{'agents.ajax_get_cities'|fn_url}';
    {literal}

    $('#client_company').change(function() {
        var company_id = $('#client_company').val();
        var data = {
            company_id : company_id
        };
        ajax_get_options(url_products, data, '#client_product');
        ajax_get_options(url_cities, data, '#client_city');
    });
    $('#client_city').change(function() {
        var company_id = $('#client_company').val();
        var data = {
            company_id : company_id,
            city_id : $(this).val()
        };
        ajax_get_options(url_products, data, '#client_product');

    });

</script>
{/literal}
<!-- PRODUCTS END -->
{include file="views/agents/pagination.tpl"}

