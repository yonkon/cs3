<div id="order_make_div">
    <form id="filters" method="post">
    <input type="hidden" name="step" value="{$step}" id="step">
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
    <input type="hidden" name="order_filepath" value="{$order_file}">

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
        <button type="button" onclick="edit_order()">{$lang.Edit}</button>      <button id="button_product" type="submit">{$lang.Send}</button>
    </div>
    </form>
</div>
{literal}
<script type="text/javascript">
    function edit_order(){
        $("#step").val(0);
        $("#filters").submit();
    }

</script>
{/literal}