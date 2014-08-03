<div id="main_content">
    <form>
        <div class="company_description_div">
            <a href="">{$lang.back_to_catalog}</a>
            <img src="" alt="{$lang.logo}">
            <p class="bold">{$company.company}</p>
            <p>{$company.company_description}</p>
        </div>
        {include file="office_description.tpl"}
        {*<div class="office_description_div">*}
            {*<a href="">{$lang.shipment_and_payment}</a> | <a href="">{$lang.about_company}</a>*}
            {*<label for="select_city">{$lang.City}</label>*}
            {*<select id="select_city" name="city">*}
                {*<option value="">{$lang.Select_city}</option>*}
                {*{foreach from=$cities item="city"}*}
                    {*<option value="{$city.city_id}" >{$city.city}</option>*}
                {*{/foreach}*}
            {*</select>*}
            {*<h2>{$shipping.shipping}</h2>*}
            {*<h2>{$office.office}</h2>*}
            {*<p>{$lang.Address}</p>*}
            {*<p class="bold">{$office.address}</p>*}
            {*<p>{$lang.phone}</p>*}
            {*<p>{$office.phone}</p>*}
            {*<p>{$office.shipment_description}</p>*}

            {*<div class="gmap"></div>*}
        {*</div>*}
        {*<div class="office_shipment_div">*}

            {*<h2>{$lang.Shipping}</h2>*}
            {*{foreach from=$shipping_descriptions item='shipping'}*}
                {*<p class="bold">{$shipping.name}</p>*}
                {*<p>{$shipping.description}</p>*}
            {*{/foreach}*}
        {*</div>*}
        <a href="">{$lang.back_to_catalog}</a>
    </form>
</div>