<div id="product_filters">
    <form method="post">
    <input type="hidden" name="dispatch" value="agents.companies_and_products">

        <select id="client_company" name="client[company]">
        <option value="">- {$lang.select_company} -</option>
        {foreach from=$companies item="company" key="code"}
            <option {if !empty($client.company) && $company.company_id == $client.company}selected="selected"{/if}  value="{$company.company_id}">{$company.company}</option>
        {/foreach}
    </select>
    <select id="client_product" name="client[product]">
        <option value="">- {$lang.select_product} -</option>
        {foreach from=$all_products item="product" key="code"}
            <option {if !empty($client.product) && $product.product_id == $client.product}selected="selected"{/if}  value="{$product.product_id}">{$product.product}</option>
        {/foreach}
    </select>

        Sort by name <select name="sort_name">
            <option></option>
            <option {if !empty($client.sort_name) && $client.sort_name == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($client.sort_name) && $client.sort_name == 'desc'}selected="selected" {/if}>desc</option>
        </select>

        price <select name="sort_price">
            <option></option>
            <option {if !empty($client.sort_price) && $client.sort_price == 'asc'}selected="selected" {/if}>asc</option>
            <option{if !empty($client.sort_price) && $client.sort_price == 'desc'}selected="selected" {/if}>desc</option>
        </select>
        profit <select name="sort_profit">
            <option></option>
            <option {if !empty($client.sort_profit) && $client.sort_profit == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($client.sort_profit) && $client.sort_profit == 'desc'}selected="selected" {/if}>desc</option>
        </select>
        Location <select name="filter_city">
            <option>Current city</option>
            <option>Other city1</option>
            <option>Other city2</option>
        </select>
        <button type="submit" value="{$lang.apply_filter}">{$lang.apply_filter}</button>
        </form>
    </div>

    {foreach from=$products item="product"}
    <form id="form_{$product.product_id}">
        <input type="hidden" name="product_id" value="{$product.product_id}">
        <input type="hidden" name="dispatch" value="agents.order_make">
    <div class="product_div">
        <table>
            <tr>
                <td> <a href="{'agents.product_info'|fn_url}&product_id={$product.product_id}"><img class="product-image" src="/images/detailed/1/{$product.image.image_path}"></a></td>
                <td colspan="2">
                    <h2><a href="{'agents.product_info'|fn_url}&product_id={$product.product_id}">{$product.product}</a></h2>
                    <div class="'product-description">{$product.full_description|unescape}</div>
                </td>
                <td>
                    <div class="product-count-buttons">
                        <a href="#" class="increase" onclick="increase_count({$product.product_id}, 1, {$product.price});">+</a>
                        <a href="#" class="decrease" onclick="increase_count({$product.product_id}, -1,{$product.price});">-</a>
                        <input type="hidden" name="item_count" id="item_{$product.product_id}_count" value='1' >
                    </div>
                    <span id="item_{$product.product_id}_count_text" class="price">{$product.price|floatval}$</span>
                    <div>
                        <button type="submit" name="checkout" value="Оформить заявку">Оформить заявку</button>
                    </div>
                    <div class="shipping">{if true || $product.free_shipping || $product.edp_shipping || $product.shipping_freight}<img class="shipping-img" src="skins/basic/customer/views/agents/components/shipping.png">{/if}
                    </div>
                </td>
            </tr>
            <tr>
                <td>{if $product.company.company_description}<a href="{'agents.company_info'|fn_url}&product_id={$product.product_id}"> <img src="{$product.company.image_path}"></a>{/if}</td>
                <td colspan="2"><div>{$product.company.company_description|default|unescape}</div></td>
                <td><span>{$product.profit}</span><br><button onclick="save_order({$product.product_id});">Сохранить в кабинете</button></td>
            </tr>
        </table>
    </div>
</form>
{/foreach}
{literal}
<script type="text/javascript">
    function save_order(product_id) {
        var $form = $('#form_'+product_id);
        $('input[name=dispatch]', $form).val('agents.order_save');
        $form.submit();
    }
</script>
{/literal}
<!-- PRODUCTS END -->
{include file="views/agents/pagination.tpl"}

