<div id="company_offices_div">

    {if $mode == 'view'}
        {include file="views/agents/components/view.tpl"}
    {elseif $mode == 'update'}
        {include file="views/agents/components/update.tpl"}
    {elseif $mode == 'sliders'}
        {include file="views/agents/sliders.tpl"}
    {/if}

</div>