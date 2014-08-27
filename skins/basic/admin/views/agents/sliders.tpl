<div style="padding: 2em">
<div class="tab-switcher clr">
    <a class="tab-switch top" href="#" onclick="sliders_switchTab('top');">{$lang.slider_top_title}</a>
    |
    <a class="tab-switch company" href="#" onclick="sliders_switchTab('company');">{$lang.slider_company_title}</a>
    |
    <a class="tab-switch products" href="#" onclick="sliders_switchTab('products');">{$lang.slider_products_title}</a>
</div>

<div class="clr padding-top-1em tab top">
    <h2 class="h2 center">{$lang.slider_top_title}</h2>
    {assign var='slider_type' value='top'}
    {foreach from=$sliders.top item='slide'}
        <div class="margin-top slider_div">
            {include file="views/agents/components/slider_form.tpl"}
        </div>
    {/foreach}
    {assign var='slide' value=null}
    <div class="margin-top slider_div">
        <h2 class="h2 center">{$lang.add}</h2>
        {include file="views/agents/components/slider_form.tpl"}
    </div>
</div>

<div class="clr padding-top-1em tab company">
    <h2 class="h2 center">{$lang.slider_company_title}</h2>
    {assign var='slider_type' value='company'}
    {foreach from=$sliders.company item='slide'}
        <div class="margin-top slider_div">
        {include file="views/agents/components/slider_form.tpl"}
        </div>
    {/foreach}
    {assign var='slide' value=null}
    <div class="margin-top slider_div">
        <h2 class="h2 center">{$lang.add}</h2>
        {include file="views/agents/components/slider_form.tpl"}
    </div>
</div>

<div class="clr padding-top-1em tab products">
    <h2 class="h2 center">{$lang.slider_products_title}</h2>
    {assign var='slider_type' value='products'}
    {foreach from=$sliders.products item='slide'}
        <div class="margin-top slider_div">
            {include file="views/agents/components/slider_form.tpl"}
        </div>
    {/foreach}
    {assign var='slide' value=null}
    <div class="margin-top slider_div">
        <h2 class="h2 center">{$lang.add}</h2>
        {include file="views/agents/components/slider_form.tpl"}
    </div>
</div>
</div>
{literal}
<script type="text/javascript">
    function sliders_switchTab(tabName) {
        $('.tab-switch').removeClass('active');
        $('.tab-switch.'+tabName).addClass('active');
        $('.tab').addClass('hidden');
        $('.tab.'+tabName).removeClass('hidden');
    }
    function switchToDetailed(el) {
        var $this = $(el);
        $this.toggleClass('thumbnail');
        if($this.hasClass('thumbnail')) {
            $this.attr('src', $this.data('thumbnail'));
        } else {
            $this.attr('src', $this.data('detailed'));
        }
    }
</script>
{/literal}
