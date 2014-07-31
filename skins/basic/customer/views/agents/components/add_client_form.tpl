<div id="agents_add_client_div">
    <a class="close right" href="#" title="{$lang.Close}">{$lang.Close}</a>
<form id="agents_add_client_form" action="{'agents.add_client_form'|fn_url}">
    <input type="hidden" name="dispatch" value="agents.add_client_form"/>

    <label for="profile_name"       >{$lang.FIO}</label>
    <input id="profile_name"        name="profile_name"         value="{if !empty($client.profile_name)}{$client.profile_name}{/if}"/>
    <label for="b_phone"            >{$lang.Phone}</label>
    <input id="b_phone"             name="b_phone"              value="{if !empty($client.b_phone)}{$client.b_phone}{/if}"/>
    <label for="b_email"            >{$lang.Mail}</label>
    <input id="b_email"             name="b_email"              value="{if !empty($client.b_email)}{$client.b_email}{/if}"/>
    {*<label for="registration_date"  >{$lang.Registration_date}  </label>*}
    {*<input id="registration_date"   name="registration_date"    value="{if !empty($client.registration_date)}   {$client.registration_date} {/if}"/>*}
    <label for="comment"            >{$lang.Comment}</label>
    <textarea id="comment"          name="comment"  >{if !empty($client.comment)}{$client.comment}{/if}</textarea>
    <button type="submit" id="submit" name="submit" value="{$lang.Submit}">{$lang.Submit}</button>
</form>
</div>