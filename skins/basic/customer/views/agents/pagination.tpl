<div class="pagination-div">
{if !empty($pagination) }
    {if empty($page)}{assign var='page' value=1}{/if}
    {if $page != 1}
        <a class="pagination previous" href="{$pagination.url}?page={$page-1}"
    {/if}
    page = {$page}
    {section name=cu loop=$pages start=1}
        <a
        class="pagination {if $page == $smarty.section.cu.index}active{/if}"
        href="{$pagination.url}?page={$smarty.section.cu.index}"
        >
            {$smarty.section.cu.index}
        </a>
        {*i={$smarty.section.cu.index}*}
    {/section}
{/if}
</div>