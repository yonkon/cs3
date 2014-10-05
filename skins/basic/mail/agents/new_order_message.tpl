{$lang.new_order_mail_body|unescape|replace:'[order_id]':$order_id }
<br>
{$lang.link}:
{if $target == 'agent'}
    <a href="{'agents.orders'|fn_url}&order_id={$order_id}">
        {'agents.orders'|fn_url}&order_id={$order_id}
    </a>
{else}
    <a href="{'orders.details'|fn_url}&order_id={$order_id}">
        {'orders.details'|fn_url}&order_id={$order_id}
    </a>
{/if}