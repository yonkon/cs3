{if !empty($product) }
<div class="product_description_div clr switchable_tab {if $active_tab != 'product'}hidden{/if}" >
    <form method="post" action="{'agents.order_make'|fn_url}">

        <a href="{'agents.companies_and_products'|fn_url}">{$lang.back_to_catalog}</a>
        <img class="block" src="{$product.image.image_path}" alt="{$lang.logo}">
        <div class="share42init"></div>
        <script type="text/javascript" src="http://agent3.ctotopru.410.com1.ru/share42/share42.js"></script>

        <p class="bold">{$product.product}</p>
        <p>{$product.description|unescape}</p>
        <p>{$lang.Price}{$product.price|format_price:$currencies.$secondary_currency:'price':"price big":true}</p>
        <p>{$lang.Profit}{$product.profit|format_price:$currencies.$secondary_currency:'profit':"price big":true}</p>

        <input type="hidden" name="product_id" value="{$product.product_id}">
        <button type="submit" value="submit" name="submit">{$lang.Make_order}</button>
    </form>
</div>
{/if}