<div id="products_section">
    <select id="client_company" name="client[company]">
        <option value="">- {$lang.select_company} -</option>
        {foreach from=$companies item="company" key="code"}
            <option {if $company.company == $client.company}selected="selected"{/if}  value="{$company.company_id}">{$company.company}</option>
        {/foreach}
    </select>
    <select id="client_product" name="client[product]">
        <option value="">- {$lang.select_product} -</option>
        {foreach from=$all_products item="product" key="code"}
            <option {if $product.product == $client.product}selected="selected"{/if}  value="{$product.product_id}">{$product.product}</option>
        {/foreach}
    </select>

    <div id="product_sorting">
        Sort by name <select name="sort_name">
            <option>asc</option>
            <option>desc</option>
        </select>

        price <select name="sort_price">
            <option>asc</option>
            <option>desc</option>
        </select>
        profit <select>
            <option>asc</option>
            <option>desc</option>
        </select>
        Location <select>
            <option>Current city</option>
            <option>Other city1</option>
            <option>Other city2</option>
        </select>
    </div>

    {foreach from=$products item="product"}
    <form>
        <input type="hidden" name="product_id" value="{$product.product_id}">
        <input type="hidden" name="dispatch" value="agents.order_make">
    <div class="product_div">
        <table>
            <tr>
                <td> <img class="product-image" src="/images/detailed/1/{$product.image.image_path}"></td>
                <td colspan="2">
                    <h2><a href="{"products.view"|fn_url}&product_id={$product.product_id}">{$product.product}</a></h2>
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
                    <div class="shipping">{if true || $product.free_shipping || $product.edp_shipping || $product.shipping_freight}<img class="shipping-img" src="design/themes/basic/templates/views/agents/images/shipping.png">{/if}
                    </div>
                </td>
            </tr>
            <tr>
                <td>{if $product.supplier.description}<img src="{$product.supplier.description}">{/if}</td>
                <td colspan="2"><div>{$product.supplier.description|default|unescape}</div></td>
                <td><span>{$product.profit}</span><br><button>Сохранить в кабинете</button></td>
            </tr>
        </table>
    </div>
</form>
{/foreach}
