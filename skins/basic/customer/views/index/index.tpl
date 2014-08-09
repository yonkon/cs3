{* Don't delete it *}
<div id="main_content">
    <div id="top">
        <ul class="hr">
            <li><a>{$lang.main}</a></li>
            <li><a>{$lang.company}</a></li>
            <li><a>{$lang.how_its_work}</a></li>
            <li><a>{$lang.about}</a></li>
            {*<li id="login"><a  href="/index.php?dispatch=auth.login_form&return_url=index.php">{$lang.login}</a></li>*}
            {if $auth.user_id}
               <li id="login"> <a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="account">{$lang.sign_out}</a></li>
            {else}
            <li id="login"> <a href="{"auth.login_form?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="account">{$lang.sign_in}</a></li>
            {/if}

        </ul>
    </div>

    <div id="content">
        <div id="content_top"></div>
        <div id="content_top_menu">Неизвестный контейнер</div>
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
        <div class="share42init"></div>
        <script type="text/javascript" src="http://agent3.ctotopru.410.com1.ru/share42/share42.js"></script>
        <div id="content_affiliate_program">
            <div>{$lang.affiliate_program}</div>
            <div>Карусель, карусель, это радость для нас...</div>
            <div><button>{$lang.view_all}</button></div>
        </div>
    </div>

    <div id="footer">
        <ul class="hr">
            <li><a>{$lang.for_affiliates}</a></li>
            <li><a>{$lang.About_project}</a></li>
            <li><a>{$lang.map_site}</a></li>
            <li><a>{$lang.Feedback}</a></li>
            <li><a>{$lang.Partnership_Agreement}</a></li>
        </ul>
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