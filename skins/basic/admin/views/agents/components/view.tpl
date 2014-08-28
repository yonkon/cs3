<div class="clr">
    <a class="back-link" href="{'companies.update'|fn_url}&company_id={$company_id}">{$lang.back}</a>
</div>
<h1 class="center h1">{$lang.Company_offices}</h1>
<div id="company_offices_list">
    <div class="right">
        <form action="{'agents.offices_add'|fn_url}">
            <input type="hidden" name="company_id" value="{$company_id}">
            <input type="hidden" name="dispatch" value="agents.offices_add">
            <button type="submit" class="add-product default-button" value="add">{$lang.Add_office}</button>
        </form>
    </div>
    {if !empty($offices)}
        <table id="company_offices_table" class="table">
            <thead>
            <tr>
                <th>{$lang.Id}</th>
                <th>{$lang.Name}</th>
                <th>{$lang.City}</th>
                <th>{$lang.Address}</th>
                <th>{$lang.Description}</th>
                <th>{$lang.Shipping}</th>
                <th>{$lang.Action}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$offices item="office"}
                <tr id="office_{$office.office_id}" data-id="{$office.office_id}">
                    <td>{$office.office_id}</td>
                    <td>{$office.office_name}</td>
                    <td>{$office.city}</td>
                    <td>{$office.address}</td>
                    <td>{$office.description|unescape}</td>
                    <td>
                        <a href="{'agents.office_shippings'|fn_url}&office_id={$office.office_id}" target="_blank">{$lang.Edit}</a>
                        {foreach from=$office.shippings item='shipping'}
                            <div class="shipping-table-info">
                                <p class="bold">{$shipping.shipping_name}</p>
                                <p class="description">{$shipping.shipping_description|unescape}</p>
                            </div>
                        {/foreach}
                    </td>
                    <td>
                        <div class="controls">
                            <a
                                    class="block"
                                    href="#"
                                    onclick="deleteOffice({$office.office_id});"
                                    >{$lang.delete}</a>
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {else}
        {$lang.Not_found}
    {/if}
</div>
{literal}
<script type="text/javascript">
    function deleteOffice(id) {
        {/literal}
        var error = "{$lang.error}";
        var url = "{'agents.ajax_office_delete'|fn_url}";
        {literal}
        $.ajax({
            url : url,
            type : "post",
            data : {
                office_id : id
            }
        }).success(function(){
            $('#office_'+id).remove();
        }).error(function(){
            alert(error);
        });
    }
</script>
{/literal}
