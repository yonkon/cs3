{* Don't delete it *}
<div id="main_content">

    <div id="content">
        {*<div id="content_top"></div>*}
        {*<div id="content_top_menu">Неизвестный контейнер</div>*}
        <div id="content_company" class="h2">{$lang.Companies_which_you_can_collaborate}</div>
        <div id="company_slaider">
            {foreach from=$company_slider.images item='image'}
                <div>
                    <img src="{$image.filename}" alt="{$image.company}">
                    {*<span class="company_name">{$image.company}</span>*}
                </div>
            {/foreach}
        </div>
        <div id="content_registration">
            <div id="partner_counter">
                {$lang.works_representatives}
                {foreach from=$total_agents item='number'}
                    {if !empty($total_agents_use_images) }
                        <img src="/skins/basic/customer/views/agents/images/numbers/{$number}.png" alt="{$number}"/>
                    {else}
                        <div class="number">{$number}</div>
                    {/if}
                {/foreach}
            </div>

            <a href="/index.php?dispatch=profiles.add&user_type=C"><button id="registration_button" class="big green float-right button">{$lang.registration}</button></a>
        </div>
        <script type="text/javascript" src="//yandex.st/share/share.js"
                charset="utf-8"></script>
        <div class="yashare-auto-init" data-yashareL10n="ru"
             data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus" data-yashareTheme="counter"

                ></div>
        <div class="h2 clear-both padding-top-2em">{$lang.affiliate_program}</div>
        <div id="content_affiliate_program">
            {foreach from=$plan_slider.images item='image'}
                <div>
                    <img src="{$image.filename}">
                    <span class="plan_name">{$image.plan}</span>
                </div>
            {/foreach}
        </div>
        <div><button id="see_all" class="big green button center-block block">{$lang.view_all}</button></div>

    </div>
</div>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/jquery.slick/1.3.6/slick.css"/>
<script type="text/javascript" src="/slick.js"></script>
{literal}
    <script type="text/javascript">
        $(document).ready(function(){
            $('#content_affiliate_program').slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 2000
            });
        });
    </script>
{/literal}
{literal}
    <script type="text/javascript">
        $(document).ready(function(){
            $('#company_slaider').slick({
                slidesToShow: 5,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 2000
            });
        });
    </script>
{/literal}