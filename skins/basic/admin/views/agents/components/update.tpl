<form id="office_add_form" method="post" action="{'agents.offices_add'|fn_url}">
    <input type="hidden" name="office[company_id]" value="{$company_id}">
    <input type="hidden" name="company_id" value="{$company_id}">
    <input type="hidden" name="dispatch" value="agents.offices_add">

    <label for="office_name"       >{$lang.Office_name}</label>
    <input id="office_name"        name="office[office_name]"         value="{if !empty($office.office_name)}{$office.office_name}{/if}"/>

    <label for="city_id">{$lang.city}</label>
    <select id="city_id" name="office[city_id]">
        <option value="">- {$lang.select_city} -</option>
        {foreach from=$cities item="city" key="code"}
            <option {if $city.CityId == $office.city_id}selected="selected"{/if}  value="{$city.CityId}">{$city.name}</option>
        {/foreach}
    </select>

    <label for="address"            >{$lang.address}</label>
    <input id="address"             name="office[address]"              value="{if !empty($office.address)}{$office.address}{/if}"/>

    <label for="phone"            >{$lang.Phone}</label>
    <input id="phone"             name="office[phone]"              value="{if !empty($office.phone)}{$office.phone}{/if}"/>

    <label for="email"            >{$lang.email}</label>
    <input id="email"             name="office[email]"              value="{if !empty($office.email)}{$office.email}{/if}"/>

    <label for="description"            >{$lang.description}</label>
    <textarea id="description"          name="office[description]"  >{if !empty($office.description)}{$office.description}{/if}</textarea>

    <button type="submit" id="submit" name="submit" value="submit">{$lang.Submit}</button>

</form>