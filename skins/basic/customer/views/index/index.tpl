{* Don't delete it *}
<div id="main_content">
    <div id="top">
        <ul>
            <li>{$lang.main}</li>
            <li>{$lang.company}</li>
            <li>{$lang.how_its_work}</li>
            <li>{$lang.about}</li>
            <li>{$lang.login}</li>
        </ul>
    </div>

    <div id="content">
        <div id="content_top">Картинка</div>
        <div id="content_top_menu">Неизвестно что</div>
        <div id="content_company">{$lang.Companies_which_you_can_collaborate}
            Карусель, карусель, это радость для нас...</div>
        <div id="content_registration">
            {$lang.works_representatives}
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <img src="" alt=""/>
            <button>{$lang.registration}</button>
        </div>
        <div id="content_social">вставь сюда полностью!</div>
        <div id="content_affiliate_program">
            <div>{$lang.affiliate_program}</div>
            <div>Карусель, карусель, это радость для нас...</div>
            <div><button>{$lang.view_all}</button></div>
        </div>
    </div>

    <div id="footer">

    </div>
</div>

<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/jquery.slick/1.3.6/slick.css"/>
<script type="text/javascript" src="/slick.js"></script>
{literal}
<script type="text/javascript">
    $(document).ready(function(){
        $('#content_affiliate_program').slick({
            slidesToShow: 2,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 2000
    });
    });
</script>
{/literal}