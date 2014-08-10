<div class="office_description_div clr">
    <div class="block clr">
        <a href="#" onclick="switch_active_tab('product');" class="switch_link product {if $active_tab == 'product'}active{/if}">{$lang.shipment_and_payment}</a>
        |
        <a href="#" onclick="switch_active_tab('company');" class="switch_link company{if $active_tab == 'company'}active{/if}">{$lang.about_company}</a>
    </div>
    <label for="select_city">{$lang.City}</label>
    <select id="select_city" name="city">
        <option value="">{$lang.Select_city}</option>
        {foreach from=$cities item="city"}
            <option value="{$city.city_id}" >{$city.city}</option>
        {/foreach}
    </select>
    <h2>{$shipping.shipping}</h2>
    <h2>{$office.office}</h2>
    <p>{$lang.Address}</p>
    <p class="bold">{$office.address}</p>
    <p>{$lang.phone}</p>
    <p>{$office.phone}</p>
    <p>{$office.shipment_description|unescape}</p>

    <div class="gmap"></div>
</div>
<div class="office_shipment_div">

    <h2>{$lang.Shipping}</h2>
    {foreach from=$shipping_descriptions item='shipping'}
        <p class="bold">{$shipping.name}</p>
        <p>{$shipping.description|unescape}</p>
    {/foreach}
</div>

{literal}
<script type="text/javascript">
    function switch_active_tab(tab_name) {
        $('.switch_link').each(function(i, el) {
            var $el = $(el);
            $el.removeClass('active')
        });
        $('.switch_link.'+tab_name).addClass('active');
        $('.switchable_tab').each(function(i, el) {
            $(el).hide();
        });
        $('.' + tab_name + '_description_div').show();
        event.preventDefault();
    }
</script>
{/literal}
