{*<div class="left_agent_menu_section">
    <ul>
        <li class="left_agent_menu_profile">
            <img src={if $user_data.avatar} "{$user_data.avatar}" {else}"noavatar.gif"{/if} class="avatar-small">
            <a href="{"profiles.update"|fn_url}" rel="nofollow" class="underlined h2 level-0">{$lang.agents_profile}</a>
        </li>
    </ul>
</div>*}
<div class="left_agent_menu_section">
    <ul>
        <li class="level-0" > <a href="{"agents.office"|fn_url}" rel="nofollow" class="agent-icon icon-office  underlined h2  {if $mode == 'office'}active{/if}">{$lang.agents_office}</a></li>
        <li class="level-1"> <a href="{"agents.companies_and_products"|fn_url}" rel="nofollow" class="agent-icon icon-products underlined h3 {if $mode == 'products'}active{/if}{if $mode == 'order_make'}active{/if}">{$lang.agents_companies_and_products}</a></li>
        <li class="level-1" > <a href="{"agents.orders"|fn_url}" rel="nofollow" class="agent-icon icon-orders  underlined h3  {if $mode == 'orders'}active{/if}">{$lang.orders}</a></li>
        <li class="level-2" > <a href="{"agents.orders_active"|fn_url}" rel="nofollow" class="agent-icon icon-orders_active  underlined h3  {if $mode == 'orders_active'}active{/if}">{$lang.orders_active}</a></li>
        <li class="level-2" > <a href="{"agents.orders_closed"|fn_url}" rel="nofollow" class="agent-icon icon-orders_closed  underlined h3  {if $mode == 'orders_closed'}active{/if}">{$lang.orders_closed}</a></li>
        <li class="level-2" > <a href="{"agents.orders_saved"|fn_url}" rel="nofollow" class="agent-icon icon-orders_saved  underlined h3  {if $mode == 'orders_saved'}active{/if}">{$lang.orders_saved}</a></li>
    </ul>
</div>
<div class="left_agent_menu_section">
    <ul>
        <li class="level-0" > <a href="{"agents.collegues"|fn_url}" rel="nofollow" class="agent-icon icon-collegues  underlined  {if $mode == 'collegues'}active{/if}">{$lang.agents_collegues}</a></li>
    </ul>
</div>
<div class="left_agent_menu_section">
    <ul>
        <li class="level-0" > <a href="{"agents.clients"|fn_url}" rel="nofollow" class="agent-icon icon-add_client_form  underlined  {if $mode == 'clients'}active{/if} {if $mode == 'add_client_form'}active{/if}">{$lang.agents_clients}</a></li>
    </ul>
</div>
<div class="left_agent_menu_section">
    <ul>
        <li class="level-0" > <a href="{"agents.report"|fn_url}" rel="nofollow" class="agent-icon icon-report  underlined  {if $mode == 'report'}active{/if}">{$lang.Reports}</a></li>
    </ul>
</div>
<div class="left_agent_menu_section">
    <ul>
        <li class="level-0" > <a href="{"agents.new_products"|fn_url}" rel="nofollow" class="agent-icon icon-new_products  underlined  {if $mode == 'new_products'}active{/if}">{$lang.whats_new}</a></li>
    </ul>
</div>
