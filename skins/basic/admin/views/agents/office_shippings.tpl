<div id="office_shippings_div" class="padding-1em">
    {*<div class="clr">*}
        {*<a class="back-link" href="{'agents.office_shippings'|fn_url}&office_id={$office_id}">{$lang.back}</a>*}
    {*</div>*}
    <h1 class="center h1">{$lang.Office_shippings}</h1>
    <form method="post" action="{'agents.office_shipping_add'|fn_url}">
        <input type="hidden" name="dispatch" value="agents.office_shipping_add">
        <input type="hidden" name="office_id" value="{$office_id}">
        <input type="hidden" name="shipping[office_id]" value="{$office_id}">
        <button class="add-product default-button right" type="submit" value="add">{$lang.Add}</button>
    </form>

    {if empty($shippings)}
        <p>{$lang.Not_found}</p>
    {else}
        <table class="table">
            <thead>
            <tr>
                <th>{$lang.ID}</th>
                <th>{$lang.Name}</th>
                <th>{$lang.Description}</th>
                <th>{$lang.Action}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$shippings item="shipping"}
                <tr id="shipping_{$shipping.shipping_id}">
                    <td>{$shipping.shipping_id}</td>
                    <td>{$shipping.shipping_name}</td>
                    <td>{$shipping.shipping_description|unescape}</td>
                    <td>
                        <div class="controls">
                            <a
                                class="block"
                                href="#"
                                onclick="deleteShipping({$shipping.shipping_id});"
                            >{$lang.delete}</a>
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/if}
</div>
{literal}
    <script type="text/javascript">
        function deleteShipping(id) {
            {/literal}
            var error = "{$lang.error}";
            var url = "{'agents.ajax_shipping_delete'|fn_url}";
            {literal}
            $.ajax({
                url : url,
                type : "post",
                data : {
                    shipping_id : id
                }
            }).success(function(){
                $('#shipping_'+id).remove();
            }).error(function(){
                alert(error);
            });
        }
    </script>
{/literal}
