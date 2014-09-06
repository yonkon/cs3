{if !empty($company) }
<div class="company_description_div clr switchable_tab {if $active_tab != 'company'}hidden{/if}">
    <a href="" class="block clr margin-bottom">{$lang.back_to_catalog}</a>
    <img class="margin-top" width="350px" height="350px" src="{$company.image_path|unescape|fn_generate_thumbnail:350:350:true|escape}" alt="{$lang.logo}">
    <p class="bold margin-top">{$company.company}</p>
    <p class="margin-bottom">{$company.company_description|unescape}</p>
    <p class="margin-bottom">{$company.company_long_description|unescape}</p>
    <p class="margin-bottom">{$lang.phone}: {$company.phone}</p>
    <p class="margin-bottom">{$lang.email}: {$company.email}</p>
    <p class="margin-bottom">{$lang.fax}: {$company.fax}</p>
    {if $company.company_home_master}<p class="margin-bottom">{$lang.company_home_master}: {$company.company_home_master_description|unescape}</p>{/if}
    <div id="company_products_div" class="margin10px">
        <table>
            <tr class="padding10px">
                <td class="center h2" colspan="2">{$lang.company_products}</td>
            </tr>
            {foreach from=$all_products item='prod'}
                <tr class="padding10px" style="border-top: 1px solid lightgray;">
                    <td>
                        <a href="{'agents.product_info'|fn_url}&product_id={$prod.product_id}">
                            <img width="50px" src="{$prod.image.image_path|unescape|fn_generate_thumbnail:50:50:true|escape}" alt="{$prod.product}">
                        </a>
                    </td>
                    <td>
                        <p>{$lang.name}: {$prod.product}</p>
                        <p>{$lang.price}: {$prod.price|floatval|format_price:$currencies.$secondary_currency:$price_id:"price":true}</p>
                    </td>
                </tr>
            {/foreach}
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
        </table>
    </div>
</div>
{/if}