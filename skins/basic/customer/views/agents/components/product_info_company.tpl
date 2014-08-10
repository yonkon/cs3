{if !empty($company) }
<div class="company_description_div clr switchable_tab {if $active_tab != 'company'}hidden{/if}">
    <a href="">{$lang.back_to_catalog}</a>
    <img src="{$company.image_path}" alt="{$lang.logo}">
    <p class="bold">{$company.company}</p>
    <p>{$company.company_description|unescape}</p>
</div>
{/if}