<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>


    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="en" />

    <!-- blueprint CSS framework -->
    <link rel="stylesheet" type="text/css" href="index.css" />

</head>
<form>
    <div id="main_content">
        <div class="company_description_div">
            <a href="">{$lang.back_to_catalog}</a>
            <img src="" ALT="{$lang.logo}">
            <p>текст компании</p>
        </div>
        <div class="office_description_div">
            <a href="">{$lang.shipment_and_payment}</a> | <a href="">{$lang.about_company}</a>
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
            <p>{$office.shipment_description}</p>

            <div class="gmap"></div>
        </div>
        <div class="office_shipment_div">

            <h2>{$lang.Shipping}</h2>
            {foreach from=$shipping_descriptions item='shipping'}
                <p class="bold">{$shipping.name}</p>
                <p>{$shipping.description}</p>
            {/foreach}
        </div>
        <a href="">{$lang.back_to_catalog}</a>
    </div>

</form>

</html>