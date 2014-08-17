<div class="pagination-div">
{if !empty($pagination.pages) }
    {if empty($pagination.page)}{assign var='pagination.page' value=1}{/if}
    {if $pagination.page != 1}
        <a class="pagination previous" href="{$pagination.url}&page={$pagination.page-1}">{$lang.Back}</a>
    {/if}
   {foreach from=$pagination.pages item='pg'}
       <a
               class="pagination{if $pagination.page == $pg} active{/if}"
               href="{$pagination.url}&page={$pg}"
               onclick="agents_go_to_page({$pg})"
               >{$pg}
       </a>
   {/foreach}
    {if $pagination.page < $pagination.total_pages }
        <a class="pagination next" href="{$pagination.url}&page={$pagination.page+1}">{$lang.Next}</a>
    {/if}

{/if}
</div>

{literal}
<script type="text/javascript">
    function agents_go_to_page(page, form_selector, page_selector) {
        form_selector = form_selector ? form_selector : '#filters';
        page_selector = page_selector ? page_selector : '#page';
        $pageInput = $(page_selector);
        $pageInput.val(page);
        $form = $(form_selector);
        if($form.length && $form.length>0) {
            if($form && $pageInput && $pageInput.length && $pageInput.length>0) {
                $form.submit();
                event.preventDefault();
                return false;
            } else {
                return true;
            }
        }
        else {
            return true;
        }
    }

</script>
{/literal}
