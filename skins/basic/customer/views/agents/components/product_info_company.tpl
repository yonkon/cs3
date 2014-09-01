{if !empty($company) }
<div class="company_description_div clr switchable_tab {if $active_tab != 'company'}hidden{/if}">
    <a href="" class="block clr margin-bottom">{$lang.back_to_catalog}</a>
    <img class="margin-top" src="{$company.image_path}" alt="{$lang.logo}">
    <p class="bold margin-top">{$company.company}</p>
    <p class="margin-bottom">{$company.company_description|unescape}</p>
    <div id="company_products_div" class="margin-top">
        <table>
            <tr class="padding10px">
                <td class="center h2" colspan="2">{$lang.company_products}</td>
            </tr>
            {foreach from=$all_products item='prod'}
                <tr class="padding-top-1em" style="border-top: 1px solid lightgray;">
                    <td>
                        <a href="{'agents.product_info'|fn_url}&product_id={$prod.product_id}">
                            <img width="{$settings.Thumbnails.product_lists_thumbnail_width}" src="{$prod.image.image_path|unescape|fn_generate_thumbnail:$settings.Thumbnails.product_lists_thumbnail_width:$settings.Thumbnails.product_lists_thumbnail_height:true|escape}" alt="{$prod.product}">
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