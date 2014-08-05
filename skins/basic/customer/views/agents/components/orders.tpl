<div id="product_filters">
    <form method="post">
    <input type="hidden" name="dispatch" value="agents.orders">

        <select id="company" name="where[company_id]">
        <option value="">- {$lang.select_company} -</option>
        {foreach from=$companies item="company" key="code"}
            <option {if !empty($where.company_id) && $company.company_id == $where.company_id}selected="selected"{/if}  value="{$company.company_id}">{$company.company}</option>
        {/foreach}
    </select>
    <select id="product" name="where[product_id]">
        <option value="">- {$lang.select_product} -</option>
        {foreach from=$products item="product" key="code"}
            <option {if !empty($where.product_id) && $product.product_id == $where.product_id}selected="selected"{/if}  value="{$product.product_id}">{$product.product}</option>
        {/foreach}
    </select>

        Sort by name <select name="order[name]">
            <option></option>
            <option {if !empty($order.name) && $order.name == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($order.name) && $order.name == 'desc'}selected="selected" {/if}>desc</option>
        </select>

        price <select name="order[price]">
            <option></option>
            <option {if !empty($order.price) && $order.price == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($order.price) && $order.price == 'desc'}selected="selected" {/if}>desc</option>
        </select>
        profit <select name="order[profit]">
            <option></option>
            <option {if !empty($order.profit) && $order.profit == 'asc'}selected="selected" {/if}>asc</option>
            <option {if !empty($order.profit) && $order.profit == 'desc'}selected="selected" {/if}>desc</option>
        </select>
        Location <select {*name="where[city]"*}>
            <option>Current city</option>
            <option>Other city1</option>
            <option>Other city2</option>
        </select>
        Status <select name="where[status]">
            <option value="">{$lang.Status}</option>
            {foreach from=$order_statuses item="status" key="code" }
                <option value="{$status.status}" {if !empty($where.status) && $status.status == $where.status}selected="selected"{/if}  >{$status.description}</option>
            {/foreach}
        </select>
        <button type="submit" value="{$lang.apply_filter}">{$lang.apply_filter}</button>
        </form>
    </div>


<div id="orders_div">
    {foreach from=$orders item="order"}
        <p>{$lang.Order} {$order.order_id} </p>
    <form>
        <input type="hidden" name="order_id" value="{$order.order_id}">
        <input type="hidden" name="dispatch" value="agents.order_make">
    <div class="order_div">
        <table>
            <tr>
                <td> <img class="product-image" src="/images/detailed/1/{$order.product_data.image.image_path}"></td>
                <td colspan="2">
                    <h2><a href="{"products.view"|fn_url}&product_id={$order.product_id}">{$order.product_data.product}</a></h2>
                    <div class="product-description">{$order.product_data.description|unescape}</div>
                </td>
                <td>
                    <span id="item_{$order.product_data.product_id}_count_text" class="price">{$order.product_data.base_price|floatval}$</span>
                    <div class="shipping">{if true || $order.product_data.free_shipping || $order.product_data.edp_shipping || $order.product_data.shipping_freight}<img class="shipping-img" src="design/themes/basic/templates/views/agents/images/shipping.png">{/if}
                    </div>
                </td>
            </tr>
            <tr>
                <td>{if $order.company_data.image_path}<img src="{$order.company_data.image_path}">{/if}</td>
                <td colspan="2"><div>{$order.company_data.company_description|default|unescape}</div></td>
                <td>
                    <span>{$order.product_data.profit}</span><br/>
                    <span class="status">{$order.status}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="underlined">{$order.b_lastname} {$order.b_firstname} {$order.b_midname}</p>
                    <p class="underlined">{$order.email}<br/> {$order.b_phone}</p>
                    <p class="underlined">{$order.registration_date|date_format}</p>
                </td>
                <td>
                    {$lang.Comment}:
                    <p class="comment" id="comment_{$order_order_id}">
                        {$order_comment}
                    </p>
                    <p class="bold">
                        {$lang.Add_comment}
                    </p>
                    <input type="text" name="comment">
                </td>
                <td>
                    <button type="submit" name="submit" value="submit">{$lang.Send}</button>
                </td>
            </tr>
        </table>
    </div>
</form>
{/foreach}
    {include file="customer/common_templates/pagination.tpl"}
</div>
