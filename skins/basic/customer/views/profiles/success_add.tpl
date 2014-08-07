{capture name="mainbox_title"}{$lang.successfully_registered}{/capture}

<span class="success-registration-text">{$lang.success_registration_text}</span>
<ul class="success-registration-list">
    {hook name="profiles:success_registration"}
        <li>
            <a href="{"profiles.update"|fn_url}">{$lang.edit_profile}</a>
            <span>{$lang.edit_profile_note}</span>
        </li>
        <li>
            <a href="{"orders.search"|fn_url}">{$lang.orders}</a>
            <span>{$lang.track_orders}</span>
        </li>
        <li>
            <a href="{"product_features.compare"|fn_url}">{$lang.compare_list}</a>
            <span>{$lang.compare_list_note}</span>
        </li>
    {/hook}
</ul>
