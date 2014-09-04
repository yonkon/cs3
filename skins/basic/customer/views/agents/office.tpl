{script src="js/ajax_get.js"}
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
    {elseif $mode == 'orders' || $mode == 'orders_active' || $mode == 'orders_closed' }
        {include file="views/agents/components/orders.tpl"}
    {elseif $mode == 'orders_saved'}
        {include file="views/agents/components/orders_saved.tpl"}
    {elseif $mode == 'collegues'}
        {include file="views/agents/components/collegues.tpl"}
    {elseif $mode == 'company_info'}
        {include file="views/agents/components/product_info_company.tpl"}
    {elseif $mode == 'product_info'}
        {include file="views/agents/components/product_info.tpl"}
    {elseif $mode == 'report'}
        {include file="views/agents/components/report.tpl"}
    {elseif $mode == 'all_plans'}
        {include file="views/agents/components/all_plans.tpl"}
    {/if}

</div>
    {if !empty($smarty.capture.agents_extra_content)}
        <div class="agents_extra_div padding10px">
            {$smarty.capture.agents_extra_content}
        </div>
    {/if}
</div>

    <div>
    <div id="social_office"> <script type="text/javascript" src="//yandex.st/share/share.js"
                  charset="utf-8"></script>
        <div class="yashare-auto-init" data-yashareL10n="ru"
             data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus" data-yashareTheme="counter"

                ></div>

    </div>
    <div class="footer_office">
        <img src=""><a href="/index.php?dispatch=support.add_ticket">{$lang.support_helpdesk}</a>
        <img src=""><a href="/index.php?dispatch=pages.view&page_id=48">{$lang.Partnership_Agreement}</a>
    </div>
    </div>
