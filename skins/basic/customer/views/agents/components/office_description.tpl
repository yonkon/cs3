<div class="company_offices_div clr">
    {if !empty($product) }
    <div class="block clr margin-top margin-bottom">
        <a href="#" onclick="switch_active_tab('product');" class="switch_link product {if $active_tab == 'product'}active{/if}">{$lang.shipment_and_payment}</a>
        |
        <a href="#" onclick="switch_active_tab('company');" class="switch_link company{if $active_tab == 'company'}active{/if}">{$lang.about_company}</a>
    </div>
    {/if}
    <div class="block clr margin-top margin-bottom">
        <label for="select_city">{$lang.City}</label>
        <select id="select_city" name="city">
            <option value="">{$lang.Select_city}</option>
            {foreach from=$cities item="city"}
                <option value="{$city.CityId}" data-lat="{$city.Latitude}" data-lng="{$city.Longitude}">{$city.name}</option>
            {/foreach}
        </select>
    </div>
    <div class="offices_descriptions_div margin-top margin-bottom" style="width: 350px; float: left">
        <h2 class="margin-top bold">{$lang.offices}</h2>
        <div id="office_names_and_descriptions">
        {foreach from=$offices item='office'}
            <p class="office_name no-padding">
                {$office.office_name}
            </p>
            <p class="office_description no-padding">
                {$office_description|unescape}
            </p>
        {/foreach}
        </div>
        <h2 class="margin-top bold">{$lang.Addresses_and_phones}</h2>
        <div id="adresses_and_phones" class="margin-top">
        {foreach from=$offices item='office'}
            <p class="no-padding office_address">{$lang.address}: {$office.address}</p>
            <p class="no-padding">{$lang.phone}: {$office.phone}</p>
            <p class="no-padding">{$lang.fax}: {$office.fax}</p>
            <p class="no-padding">{$lang.working_mode}: {$office.working_mode|unescape}</p>
            <br/>
        {/foreach}
        </div>
    </div>

    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
    {script src="js/gmaps.js"}

    <div id="offices_gmap" class="gmap" style="width: 300px; height:300px; float: left;"></div>
</div>
<div class="office_shipping_div clr">
    <h2 class="bold">{$lang.Shipping}</h2>
    <div id="shipping_names_and_descriptions">
    {foreach from=$offices item='office'}
        {foreach from=$office.shippings item='shipping'}
            <div class="shipping_name_and_description margin-top">
                <p>{$shipping.shipping_name}</p>
                <p>{$shipping.shipping_description|unescape}</p>
            </div>
        {/foreach}
    {/foreach}
    </div>
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
        if(!parseInt($city.val())) {
            return false;
        }
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
        $('#select_city').change(function(){
            var city_id = $(this).val();

            if(parseInt(city_id)) {
                var url = '{/literal}{'agents.ajax_get_offices'|fn_url}{literal}';
                var data = {
                    {/literal}
                    company_id: {$company.company_id},
                    city_id: city_id,
                    {literal}
                };

                var callback = function(data) {
                    var $names_and_descr = $('#office_names_and_descriptions');
                    var $adresses_and_phones = $('#adresses_and_phones');
                    var $shippings = $('#shipping_names_and_descriptions');
                    $adresses_and_phones.html('');
                    $names_and_descr.html('');
                    $shippings.html('');
                    for(var i=0; i<data.length; i++) {
                        var dt = data[i];

                        var p = document.createElement('p');
                        $names_and_descr.append(p);
                        var $p = $(p);
                        $p.addClass('office_name no-padding');
                        $p.text(dt.office_name);

                        p = document.createElement('p');
                        $names_and_descr.append(p);
                        $p = $(p);
                        $p.addClass('office_description no-padding');
                        $p.html(dt.description);

                        var br = document.createElement('br');
                        $names_and_descr.append(br);

                        p = document.createElement('p');
                        $adresses_and_phones.append(p);
                        $p = $(p);
                        $p.addClass('bold no-padding office_address');
                        $p.text(dt.address);

                        p = document.createElement('p');
                        $adresses_and_phones.append(p);
                        $p = $(p);
                        $p.addClass('no-padding');
                        $p.text(dt.phone);

                        br = document.createElement('br');
                        $adresses_and_phones.append(br);

                        for(var j=0; j<dt.shippings.length; j++) {
                            var sh = dt.shippings[j];
                            p = document.createElement('p');
                            $shippings.append(p);
                            $p = $(p);
                            $p.addClass('bold');
                            $p.text(sh.shipping_name);

                            p = document.createElement('p');
                            $shippings.append(p);
                            $p = $(p);
                            $p.html(sh.shipping_description);
                        }
                    }
                    set_map_coords(gmap);
                };
                ajax_get_data(url, data, callback);
            }

        });
    });
</script>
{/literal}
