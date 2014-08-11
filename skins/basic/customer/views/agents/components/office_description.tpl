<div class="company_offices_div clr">
    <div class="block clr">
        <a href="#" onclick="switch_active_tab('product');" class="switch_link product {if $active_tab == 'product'}active{/if}">{$lang.shipment_and_payment}</a>
        |
        <a href="#" onclick="switch_active_tab('company');" class="switch_link company{if $active_tab == 'company'}active{/if}">{$lang.about_company}</a>
    </div>
    <div class="block clr">
        <label for="select_city">{$lang.City}</label>
        <select id="select_city" name="city">
            <option value="">{$lang.Select_city}</option>
            {foreach from=$cities item="city"}
                <option value="{$city.CityId}" data-lat="{$city.Latitude}" data-lng="{$city.Longitude}">{$city.name}</option>
            {/foreach}
        </select>
    </div>
    <div class="offices_descriptions_div" style="width: 350px; float: left">
        <h2>{$lang.offices}</h2>
        {foreach from=$offices item='office'}
            <p class="office_name no-padding">
                {$office.office_name}
            </p>
            <p class="office_description no-padding">
                {$office_description|unescape}
            </p>
        {/foreach}

        <h2>{$lang.Addresses_and_phones}</h2>
        {foreach from=$offices item='office'}
            <p class="bold no-padding office_address">{$office.address}</p>
            <p class="no-padding">{$office.phone}</p>
            <br/>
        {/foreach}
    </div>

    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
    {script src="js/gmaps.js"}

    <div id="offices_gmap" class="gmap" style="width: 350px; height:350px; float: left;"></div>
</div>
<div class="office_shipping_div clr">
    <h2>{$lang.Shipping}</h2>
    {foreach from=$offices item='office'}
        {foreach from=$office.shippings item='shipping'}
            <p class="bold">{$shipping.shipping_name}</p>
            <p>{$shipping.shipping_description|unescape}</p>
        {/foreach}
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

    function set_map_coords(gmap) {
        gmap.removeMarkers();
        var $city = $('#select_city option:selected');
        gmap.setCenter($city.data('lat'), $city.data('lng'));
        $('.office_address').each(function(i, el) {
            var $el = $(el);
            GMaps.geocode({
                address: $el.text() + ',' + $city.text(),
                callback: function(results, status) {
                    if (status == 'OK') {
                        var latlng = results[0].geometry.location;
                        gmap.addMarker({
                            lat: latlng.lat(),
                            lng: latlng.lng()
                        });
                        gmap.fitZoom();
                    }
                }
            });
        });

    }

    var gmap = new GMaps({
        div: '#offices_gmap',
        lat: 55.75,
        lng: 37.583
    });
    $(document).ready(function() {
        $('#select_city').change(function(){set_map_coords(gmap);});
    });
</script>
{/literal}
