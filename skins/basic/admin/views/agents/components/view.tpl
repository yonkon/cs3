<div id="company_offices_list">
    <div class="right">
        <form action="{'agents.offices_add'|fn_url}">
            <input type="hidden" name="company_id" value="{$company_id}">
            <input type="hidden" name="dispatch" value="agents.offices_add">
            <button type="submit" class="add-product" value="add">{$lang.Add_office}</button>
        </form>
    </div>
    {if !empty($offices)}
        <table id="company_offices_table">
            <thead>
            <tr>
                <th>{$lang.Id}</th>
                <th>{$lang.Name}</th>
                <th>{$lang.City}</th>
                <th>{$lang.Address}</th>
                <th>{$lang.Description}</th>
                <th>{$lang.Shipping}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$offices item="office"}
                <tr data-id="{$office.office_id}">
                    <td>{$office.office_id}</td>
                    <td>{$office.office_name}</td>
                    <td>{$office.city}</td>
                    <td>{$office.address}</td>
                    <td>{$office.description|unescape}</td>
                    <td>
                        {foreach from=$office.shippings item='shipping'}
                            <div class="shipping-table-info">
                                <p class="bold">$shipping.shipping_name</p>
                                <p class="description">$shipping.shipping_description</p>
                            </div>
                        {/foreach}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {else}
        {$lang.Not_found}
    {/if}


</div>