<div id="order_make_div">
    <form method="post">
    <input type="hidden" name="step" value="{$step}">
    <input type="hidden" name="client[affiliate_id]" value="{$auth.user_id}">
    <input type="hidden" id="client_fio" name="client[fio]" value="{$client.fio }">
    <input type="hidden" id="client_phone" name="client[phone]" value="{$client.phone }">
    <input type="hidden" id="client_email" name="client[email]" value="{$client.email}">
    <input type="hidden" id="client_company" name="client[company]" value="{$client.company}">
    <input type="hidden" id="client_region" name="client[region]" value="{$client.region}">
    <input type="hidden" id="client_city" name="client[city]" value="{$client.city}">
    <input type="hidden" id="client_office" name="client[office]" value="{$client.office}">
    <input type="hidden" id="client_company" name="client[company]" value="{$client.company}">
    <input type="hidden" id="client_need_shipment" name="client[need_shipment]" value="{$client.need_shipment}">
    <input type="hidden" id="client_comment" name="client[comment]" value="{$client.comment}">
    <input type="hidden" name="step" value="{$step}">
    <div id="order_make_top">
        <h2 id="h2 lightbox-header-text">{$lang.New_order_Approvement}</h2>
        {*<img src="/images/close.png" class="close" alt="{$lang.close}">*}
        <p>{$lang.Verify_client_data_please}</p>
    </div>
    <div id="order_make_content">
        <div>
            <span class="client_filds_add_product_label">{$lang.FIO}</span>
            <span class="client_filds_add_product" id="client_fio" >{$client.fio }</span>
        </div>
        <div>
            <span class="client_filds_add_product_label">{$lang.address}</span>
            <span class="client_filds_add_product" id="client_address">{$client.address} </span>
        </div>
        <div>
            <span class="client_filds_add_product_label">{$lang.Phone}</span>
            <span class="client_filds_add_product" id="client_phone">{$client.phone } </span>
        </div>

    </div>
    <div id="order_make_bottom">
        <button id="button_product" type="button" onclick="window.history.go(-1);">{$lang.Edit}</button>      <button id="button_product" type="submit">{$lang.Send}</button>
    </div>
    </form>
</div>
