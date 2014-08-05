<div id="main_content">
    <form>
        <div class="product_description_div">
            <a href="">{$lang.back_to_catalog}</a>
            <img src="" alt="{$lang.logo}">
            <p class="bold">{$product.product}</p>
            <p>{$product.description|unescape}</p>
        </div>
        {include file="office_description.tpl"}

        <a href="{'agents.companies_and_products'|fn_url}">{$lang.back_to_catalog}</a>
    </form>
</div>