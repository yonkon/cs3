{capture name="mainbox_title"}{fn_get_lang_var("successfully_registered")}{/capture}

<span class="success-registration-text">{fn_get_lang_var("success_registration_text")}</span>
<ul class="success-registration-list">
    {hook name="profiles:success_registration"}
        <li>
            <a href="{"profiles.update"|fn_url}">{fn_get_lang_var("edit_profile")}</a>
            <span>{fn_get_lang_var("edit_profile_note")}</span>
        </li>
        <li>
            <a href="{"orders.search"|fn_url}">{fn_get_lang_var("orders")}</a>
            <span>{fn_get_lang_var("track_orders")}</span>
        </li>
        <li>
            <a href="{"product_features.compare"|fn_url}">{fn_get_lang_var("compare_list")}</a>
            <span>{fn_get_lang_var("compare_list_note")}</span>
        </li>
    {/hook}
</ul>
