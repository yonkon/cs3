{* Don't delete it *}
<div id="main_content">

    <div id="content">
        {*<div id="content_top"></div>*}
        {*<div id="content_top_menu">Неизвестный контейнер</div>*}
        <div id="content_company">{$lang.Companies_which_you_can_collaborate}</div>
           <div id="company_slaider">
               <div><img src="http://bestcoldness.com.ua/wp-content/uploads/logotip-samsung-150x150.jpg"></div>
               <div><img src="http://bestcoldness.com.ua/wp-content/uploads/logotip-samsung-150x150.jpg"></div>
               <div><img src="http://bestcoldness.com.ua/wp-content/uploads/logotip-samsung-150x150.jpg"></div>
               <div><img src="http://bestcoldness.com.ua/wp-content/uploads/logotip-samsung-150x150.jpg"></div>
               <div><img src="http://bestcoldness.com.ua/wp-content/uploads/logotip-samsung-150x150.jpg"></div>
               <div><img src="http://bestcoldness.com.ua/wp-content/uploads/logotip-samsung-150x150.jpg"></div>
               <div><img src="http://bestcoldness.com.ua/wp-content/uploads/logotip-samsung-150x150.jpg"></div>
           </div>
        <div id="content_registration">
            {$lang.works_representatives}
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <a href="/index.php?dispatch=profiles.add&user_type=C"><button id="regisration_button" style="float: right;">{$lang.registration}</button></a>
        </div>
        <script type="text/javascript" src="//yandex.st/share/share.js"
                charset="utf-8"></script>
        <div class="yashare-auto-init" data-yashareL10n="ru"
             data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,gplus" data-yashareTheme="counter"

                ></div>
        <div>{$lang.affiliate_program}</div>
        <div id="content_affiliate_program">
            <div><img src="http://sabsait.ru/wp-content/uploads/2014/05/Partnerskaya-programma-ot-uslugi-150x150.jpg">вамвам</div>
            <div><img src="http://sabsait.ru/wp-content/uploads/2014/05/Partnerskaya-programma-ot-uslugi-150x150.jpg">вамвам</div>
            <div><img src="http://sabsait.ru/wp-content/uploads/2014/05/Partnerskaya-programma-ot-uslugi-150x150.jpg">вамвамва</div>
            <div><img src="http://sabsait.ru/wp-content/uploads/2014/05/Partnerskaya-programma-ot-uslugi-150x150.jpg">вамвамвам</div>


    </div>
        <div><button id="see_all">{$lang.view_all}</button></div>

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