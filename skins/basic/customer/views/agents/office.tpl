<style type="text/css">
    {literal}

    {/literal}
</style>
{script src="js/ajax_get.js"}
{*<div id="main_content">*}
    {*<div id="top">*}
        {*<ul class="hr">*}
            {*<li><a href="/">{$lang.main}</a></li>*}
            {*<li><a>{$lang.company}</a></li>*}
            {*<li><a>{$lang.how_its_work}</a></li>*}
            {*<li><a>{$lang.contacts}</a></li>*}
            {*<li id="login"><a  href="/index.php?dispatch=auth.login_form&return_url=index.php">{$lang.login}</a></li>*}
            {*{if $auth.user_id}*}
                {*<li id="login"> <a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="account">{$lang.sign_out}</a></li>*}
            {*{else}*}
                {*<li id="login"> <a href="{"auth.login_form?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="account">{$lang.sign_in}</a></li>*}
            {*{/if}*}

        {*</ul>*}
    {*</div>*}
    {*<div id="content">*}
        {*<div id="content_top"></div>*}
        {*<div id="content_top_menu">Неизвестный контейнер</div></div>*}
<div id="office_content">
<div id="left_agent_menu">
    {include file="views/agents/components/left_agent_menu.tpl"}
</div>

<div id="agents_content">
    {$agents_content}
    {if $mode == 'products'}
        {include file="views/agents/components/products.tpl"}
    {elseif $mode == 'order_make'}
        {if $step == 1}
            {include file="views/agents/components/order_make.tpl"}
        {elseif $step == 2 }
            {include file="views/agents/components/order_make2.tpl"}
        {/if}
    {elseif $mode == 'clients'}
        {include file="views/agents/components/clients.tpl"}
    {elseif $mode == 'add_client_form'}
        {include file="views/agents/components/add_client_form.tpl"}
    {elseif $mode == 'orders'}
        {include file="views/agents/components/orders.tpl"}
    {elseif $mode == 'orders_saved'}
        {include file="views/agents/components/orders.tpl"}
    {elseif $mode == 'collegues'}
        {include file="views/agents/components/collegues.tpl"}
    {elseif $mode == 'company_info'}
        {include file="views/agents/components/product_info_company.tpl"}
    {elseif $mode == 'product_info'}
        {include file="views/agents/components/product_info.tpl"}
    {/if}

</div>
</div>
    <div id="footer_office">
    <div id="social_office"> <script type="text/javascript" src="//yandex.st/share/share.js"
                  charset="utf-8"></script>
        <div class="yashare-auto-init" data-yashareL10n="ru"
             data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus" data-yashareTheme="counter"

                ></div>

    </div>
    <div>
        <img src=""><a href="/index.php?dispatch=support.add_ticket">{$lang.support_helpdesk}</a>
        <img src=""><a href="/index.php?dispatch=pages.view&page_id=48">{$lang.Partnership_Agreement}</a>
    </div>
    </div>