<div id="main_content">
    <form>
        <div class="company_description_div">
            <a href="">{$lang.back_to_catalog}</a>
            <img src="" alt="{$lang.logo}">
            <p class="bold">{$company.company}</p>
            <p>{$company.company_description}</p>
        </div>
        {include file="office_description.tpl"}

        <a href="{'agents.companies_and_products'|fn_url}">{$lang.back_to_catalog}</a>
    </form>
</div>