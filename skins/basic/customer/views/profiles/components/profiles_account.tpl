{if !$nothing_extra}
	{include file="common_templates/subheader.tpl" title=$lang.user_account_info}
{/if}

{hook name="profiles:account_info"}
    {if false}
{*{if $settings.General.use_email_as_login != "Y"}*}
	{*<div class="form-field">*}
		{*<label for="user_login_profile" class="cm-required cm-trim">{$lang.username}</label>*}
		{*<input id="user_login_profile" type="text" name="user_data[user_login]" size="32" maxlength="32" value="{$user_data.user_login}" class="input-text" />*}
	{*</div>*}
{*{/if}*}

{*{if $settings.General.use_email_as_login == "Y" || $nothing_extra || "CHECKOUT"|defined}*}
	{*<div class="form-field">*}
		{*<label for="email" class="cm-required cm-email cm-trim">{$lang.email}</label>*}
		{*<input type="text" id="email" name="user_data[email]" size="32" maxlength="128" value="{$user_data.email}" class="input-text" />*}
	{*</div>*}
{*{/if}*}

<div class="form-field">
	<label for="password1" class="cm-required cm-password">{$lang.password}</label>
	<input type="password" id="password1" name="user_data[password1]" size="32" maxlength="32" value="{if $mode == "update"}            {/if}" class="input-text cm-autocomplete-off" />
</div>

<div class="form-field">
	<label for="password2" class="cm-required cm-password">{$lang.confirm_password}</label>
	<input type="password" id="password2" name="user_data[password2]" size="32" maxlength="32" value="{if $mode == "update"}            {/if}" class="input-text cm-autocomplete-off" />
</div>
{/if}
    <div class="clearfix">
        <div class="control-group profile-field-wrap first-name">
            <label for="firstname" class="control-label cm-required ">Имя</label>
            <input x-autocompletetype="given-name" type="text" id="firstname" name="user_data[firstname]" size="32" value="{$user_data.firstname}"
                   class="input-text ">
        </div>
        <div class="control-group profile-field-wrap last-name">
            <label for="lastname" class="control-label cm-required ">Фамилия</label>
            <input x-autocompletetype="surname" type="text" id="lastname" name="user_data[lastname]" size="32" value="{$user_data.lastname}"
                   class="input-text ">
        </div>
        <div class="control-group profile-field-wrap mid-name">
            <label for="midname" class="control-label cm-required ">Отчество</label>
            <input type="text" id="midname" name="user_data[midname]" size="32" value="{$user_data.midname}"
                   class="input-text ">
        </div>
        <div class="control-group profile-field-wrap city">
            <label for="city" class="control-label cm-required ">Ваш город</label>
            <input type="text" id="city" name="user_data[city]" size="32" value="{$user_data.city}"
                   class="input-text ">
        </div>
        <div class="control-group profile-field-wrap phone">
            <label for="phone" class="control-label cm-required ">Ваш телефон</label>
            <input x-autocompletetype="phone-full" type="text" id="phone" name="user_data[phone]" size="32" value="{$user_data.phone}"
                   class="input-text ">
        </div>
        <div class="control-group profile-field-wrap phone">
        <label for="email" class="cm-required cm-email cm-trim">{$lang.email}</label>
        <input type="text" id="email" name="user_data[email]" size="32" maxlength="128" value="{$user_data.email}" class="input-text" />
        </div>

    </div>
{/hook}

