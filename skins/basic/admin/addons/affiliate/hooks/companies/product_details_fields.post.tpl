<div class="form-field">
    <label>{$lang.product_offices}</label>
    <table id="enabled_offices" class="table">
        <thead>
        <tr>
            <th>{$lang.office_name}</th>
            <th>{$lang.office_address}</th>
            <th>{$lang.enabled}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$company_offices item="office"}
            <tr>
                <td>{$office.office_name}</td>
                <td>{$office.office_address}</td>
                <td>
                    <input
                            id="office_checkbox_{$office.office_id}"
                            type="checkbox"
                            {if in_array($office.office_id, $product_offices )}
                                checked="checked"
                            {/if}
                            onchange="toggleProductOffice({$office.office_id})" >
                </td>
            </tr>
        {/foreach}
        <tr>
            <td colspan="3" style="padding: 0; font-size: 1px;">&nbsp;</td>
        </tr>
        </tbody>
    </table>


</div>
<script type="text/javascript">
    var toggleOfficeUrl = '{'agents.product_offices'|fn_url}';
    var pid = {$product_data.product_id};
    {literal}
    function toggleProductOffice(id) {
        var enabled = !!$('#office_checkbox_'+id).is(':checked');
        $.ajax({
            url: toggleOfficeUrl,
            data: {
                product_id: pid,
                office_id: id,
                ajax: true,
                enabled: enabled
            },
            method: 'post'
        });
    }
    {/literal}
</script>