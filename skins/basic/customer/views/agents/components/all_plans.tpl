<div>
    <table>
        <thead>
        <tr>
            <th class="padding10px">{$lang.logo}</th>
            <th class="padding10px">{$lang.plan_name}</th>
            <th class="padding10px">{$lang.description}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$plans item="plan"}
            <tr id="plan_row_{$plan.plan_id}">
                <td class="padding10px" style="width: 20%;"><img style="width: 100%;" src="{$plan.filename}"></td>
                <td  class="padding10px" style="width: 20%;">{$plan.plan}</td>
                <td class="padding10px" style="width: 55%;">{$plan.description|unescape}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    </table>
</div>


