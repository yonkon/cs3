<div class="clear">
    <div class="float-left">
        {include file="common_templates/fileuploader.tpl" var_name=$slider_type}
    </div>
    <div class="float-left attach-images-alt logo-image">
        {if $slide}
            <img class="solid-border" src="{$slide.filename}" width="{$slide.width}" height="{$slide.height}" alt="{$slide.alt}" />
        {else}
            <img class="logo-empty" src="{$config.no_image_path}" />
        {/if}
        <p class="label">{$lang.alt_text}:</p>
        <p class="input-text cm-image-field"> {$slide.alt|default:$manifests[$m.skin][$m.name].alt}</p>
    </div>
    <div class="float-left">
        <p class="bold">{$lang.name}<span class="name">{$slide.name}</span></p>
        <p class="bold">{$lang.description}<span class="name">{$slide.description}</span></p>
    </div>
</div>