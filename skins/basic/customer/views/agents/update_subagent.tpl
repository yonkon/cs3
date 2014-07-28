{include file="views/profiles/components/profiles_scripts.tpl"}

{if $runtime.mode == "add" && $settings.General.quick_registration == "Y"}
    <div class="account form-wrap">

        <form name="profiles_register_form" action="{""|fn_url}" method="post">
            {*{include file="views/agents/components/profile_fields.tpl" section="C" nothing_extra="Y"}*}
            {include file="views/agents/components/profiles_account.tpl" nothing_extra="Y" location="checkout"}
            <input type="hidden" name="user_data[password1]" value="guest">
            <input type="hidden" name="user_data[password2]" value="guest">
            <input type="hidden" name="user_data[curator_id]" value="{$user_data.curator_id}">
            <input type="hidden" name="user_data[company]" value="{$user_data.company}">
            <input type="hidden" name="user_data[company_id]" value="{$user_data.company_id}">
            {hook name="checkout:checkout_steps"}{/hook}

            {include file="common/image_verification.tpl" option="use_for_register" align="left" assign="image_verification"}
            {if $image_verification}
                <div class="control-group">
                    {$image_verification nofilter}
                </div>
            {/if}

            <div class="buttons-container left">
                {include file="buttons/register_profile.tpl" but_name="dispatch[agents.update]"}
            </div>
        </form>
    </div>
    {capture name="mainbox_title"}{$lang.register_new_account}{/capture}
{else}

    {capture name="tabsbox"}
        <div class="account form-wrap" id="content_general">
            <form name="profile_form" action="{""|fn_url}" method="post">
                <input id="selected_section" type="hidden" value="general" name="selected_section"/>
                <input id="default_card_id" type="hidden" value="" name="default_cc"/>
                <input type="hidden" name="profile_id" value="{$user_data.profile_id}" />
                {capture name="group"}
                    {include file="views/agents/components/profiles_account.tpl"}
                    {*{include file="views/agents/components/profile_fields.tpl" section="C" title=$lang.contact_information}*}

                    {if false && $profile_fields.B || $profile_fields.S}
                        {if $settings.General.user_multiple_profiles == "Y" && $runtime.mode == "update"}
                            <p>{$lang.text_multiprofile_notice}</p>
                            {include file="views/agents/components/multiple_profiles.tpl" profile_id=$user_data.profile_id}
                        {/if}

                        {if $settings.General.address_position == "billing_first"}
                            {assign var="first_section" value="B"}
                            {assign var="first_section_text" value=$lang.billing_address}
                            {assign var="sec_section" value="S"}
                            {assign var="sec_section_text" value=$lang.shipping_address}
                            {assign var="body_id" value="sa"}
                        {else}
                            {assign var="first_section" value="S"}
                            {assign var="first_section_text" value=$lang.shipping_address}
                            {assign var="sec_section" value="B"}
                            {assign var="sec_section_text" value=$lang.billing_address}
                            {assign var="body_id" value="ba"}
                        {/if}

                        {include file="views/agents/components/profile_fields.tpl" section=$first_section body_id="" ship_to_another=true title=$first_section_text}
                        {include file="views/agents/components/profile_fields.tpl" section=$sec_section body_id=$body_id ship_to_another=true title=$sec_section_text address_flag=$profile_fields|fn_compare_shipping_billing ship_to_another=$ship_to_another}
                    {/if}

                    <input type="hidden" name="user_data[password1]" value="guest">
                    <input type="hidden" name="user_data[password2]" value="guest">
                    <input type="hidden" name="user_data[curator_id]" value="{$user_data.curator_id}">
                    <input type="hidden" name="user_data[company]" value="{$user_data.company}">
                    <input type="hidden" name="user_data[company_id]" value="{$user_data.company_id}">

                    {hook name="profiles:account_update"}
                    {/hook}

                    {include file="common/image_verification.tpl" option="use_for_register" align="center"}

                {/capture}
                {$smarty.capture.group nofilter}

                <div class="buttons-container left">
                    {if $runtime.mode == "add"}
                        {include file="buttons/register_profile.tpl" but_name="dispatch[agents.update]" but_id="save_profile_but"}
                    {else}
                        {include file="buttons/save.tpl" but_name="dispatch[agents.update]" but_id="save_profile_but"}
                        <input class="account-cancel" type="reset" name="reset" value="{$lang.revert}" id="shipping_address_reset"/>

                        <script type="text/javascript">
                            //<![CDATA[
                            (function(_, $) {
                                var address_switch = $('input:radio:checked', '.address-switch');
                                $("#shipping_address_reset").on("click", function(e) {
                                    setTimeout(function() {
                                        address_switch.click();
                                    }, 50);
                                });
                            }(Tygh, Tygh.$));
                            //]]>
                        </script>
                    {/if}
                </div>
            </form>
        </div>

        {capture name="additional_tabs"}
            {if $runtime.mode == "update"}
                {if !"ULTIMATE:FREE"|fn_allowed_for}
                    {if $usergroups && !$user_data|fn_check_user_type_admin_area}
                        <div id="content_usergroups">
                            <table class="table table-width">
                                <tr>
                                    <th style="width: 30%">{$lang.usergroup}</th>
                                    <th style="width: 30%">{$lang.status}</th>
                                    {if $settings.General.allow_usergroup_signup == "Y"}
                                        <th style="width: 40%">{$lang.action}</th>
                                    {/if}
                                </tr>
                                {foreach from=$usergroups item=usergroup}
                                    {if $user_data.usergroups[$usergroup.usergroup_id]}
                                        {assign var="ug_status" value=$user_data.usergroups[$usergroup.usergroup_id].status}
                                    {else}
                                        {assign var="ug_status" value="F"}
                                    {/if}
                                    {if $settings.General.allow_usergroup_signup == "Y" || $settings.General.allow_usergroup_signup != "Y" && $ug_status == "A"}
                                        <tr {cycle values=",class=\"table-row\""}>
                                            <td>{$usergroup.usergroup}</td>
                                            <td class="center">
                                                {if $ug_status == "A"}
                                                    {$lang.active}
                                                    {assign var="_link_text" value=$lang.remove}
                                                    {assign var="_req_type" value="cancel"}
                                                {elseif $ug_status == "F"}
                                                    {$lang.available}
                                                    {assign var="_link_text" value=$lang.join}
                                                    {assign var="_req_type" value="join"}
                                                {elseif $ug_status == "D"}
                                                    {$lang.declined}
                                                    {assign var="_link_text" value=$lang.join}
                                                    {assign var="_req_type" value="join"}
                                                {elseif $ug_status == "P"}
                                                    {$lang.pending}
                                                    {assign var="_link_text" value=$lang.cancel}
                                                    {assign var="_req_type" value="cancel"}
                                                {/if}
                                            </td>
                                            {if $settings.General.allow_usergroup_signup == "Y"}
                                                <td>
                                                    <a class="cm-ajax" data-ca-target-id="content_usergroups" href="{"agents.usergroups?usergroup_id=`$usergroup.usergroup_id`&type=`$_req_type`"|fn_url}">{$_link_text}</a>
                                                </td>
                                            {/if}
                                        </tr>
                                    {/if}
                                {/foreach}
                                <tr class="table-footer">
                                    <td colspan="{if $settings.General.allow_usergroup_signup == "Y"}3{else}2{/if}">&nbsp;</td>
                                </tr>
                            </table>
                            <!--content_usergroups--></div>
                    {/if}
                {/if}

                {hook name="profiles:tabs"}
                {/hook}
            {/if}
        {/capture}

        {$smarty.capture.additional_tabs nofilter}

    {/capture}

    {if $smarty.capture.additional_tabs|trim != ""}
        {include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}
    {else}
        {$smarty.capture.tabsbox nofilter}
    {/if}

    {capture name="mainbox_title"}{$lang.profile_details}{/capture}
{/if}