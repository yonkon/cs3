<div id="office_shippings_div" class="padding-1em">
    <div class="clr">
        <a class="back-link" href="{'agents.office_shippings'|fn_url}&office_id={$office_id}">{$lang.back}</a>
    </div>

    <h1 class="center h1">{$lang.Office_shipping_add}</h1>

<form id="shipping_add_form" method="post" action="{'agents.office_shipping_add'|fn_url}">
    <input type="hidden" name="shipping[office_id]" value="{$office_id}">
    <input type="hidden" name="office_id" value="{$office_id}">
    <input type="hidden" name="dispatch" value="agents.office_shipping_add">
    {if !empty($shipping.shipping_id)}
        <input type="hidden" name="shipping[shipping_id]" value="{$shipping.shipping_id}">
    {/if}

    <div class="form-field">
        <label for="shipping_name"       >{$lang.Name}</label>
        <input id="shipping_name"        name="shipping[shipping_name]"         value="{if !empty($shipping.shipping_name)}{$shipping.shipping_name}{/if}"/>
    </div>
    <div class="form-field">
        <label for="shipping_description"            >{$lang.description}</label>
        <textarea id="shipping_description" class="cm-wysiwyg"   name="shipping[shipping_description]"  >{if !empty($shipping.shipping_description)}{$shipping.shipping_description|unescape}{/if}</textarea>
    </div>

    <button class="default-button" type="submit" id="submit" name="submit" value="submit">{$lang.Submit}</button>

</form>



</div>