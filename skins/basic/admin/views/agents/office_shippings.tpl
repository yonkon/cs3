<div id="office_shippings_div">
    <h1 class="center h1">{$lang.Office_shippings}</h1>
    <form method="post" action="{'agents.office_shipping_add'|fn_url}">
        <input type="hidden" name="dispatch" value="agents.office_shipping_add">
        <input type="hidden" name="office_id" value="{$office_id}">
        <input type="hidden" name="shipping[office_id]" value="{$office_id}">
        <button class="add-product right" type="submit" value="add">{$lang.Add}</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>{$lang.ID}</th>
            <th>{$lang.Name}</th>
            <th>{$lang.Description}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$shippings item="shipping"}
            <tr>
                <td>{$shipping.shipping_id}</td>
                <td>{$shipping.shipping_name}</td>
                <td>{$shipping.shipping_description|unescape}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>


</div>