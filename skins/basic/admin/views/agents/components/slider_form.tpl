<section>
    <form method="post">
    <input type="hidden" id="slide_id" name="slide_id" value="{$slide.slide_id|default:''}">
    <input type="hidden" id="dispatch" name="dispatch" value="{'agents.sliders'|fn_url}">
    <div class="clear">
        <div class="float-left">
            {assign var="fileuploader_slide_id" value=$slide.slide_id|default:'0'}
            {assign var='fileuploader_id' value=$slider_type|cat:"["|cat:$fileuploader_slide_id|cat:"]"}
            {include file="common_templates/fileuploader.tpl" var_name=$fileuploader_id}
        </div>
        <div class="float-left attach-images-alt logo-image">
            {if !empty($slide) && !empty($slide.filename|default:'')}
                <img
                        class="solid-border thumbnail"
                        data-detailed="{$slide.filename_original}"
                        data-thumbnail="{$slide.filename}"
                        src="{$slide.filename}"
                        alt="{$slide.alt}"
                        {literal}
                        onclick="switchToDetailed(this);"
                        {/literal}
                        />

            {else}
                <img class="logo-empty" src="{$config.no_image_path}" />
            {/if}
            <label for="alt_{$slider_type}{$slide.slide_id|default:''}">{$lang.alt_text}:</label>
            <input type="text" class="input-text cm-image-field" id="alt_{$slider_type}{$slide.slide_id|default:''}" name="{$slider_type}[alt]" value="" />
        </div>
        <div class="float-left">
            <div class="form-field">
                <label for="name_{$slider_type}{$slide.slide_id|default:''}">{$lang.name}</label>
                <input type="text" class="input-text-100" name="{$slider_type}[name]" id="name_{$slider_type}{$slide.slide_id|default:''}" value="{$slide.name|default:''}">
            </div>
            <div class="form-field">
                <label for="description_{$slider_type}{$slide.slide_id|default:''}">{$lang.description}</label>
                <textarea class="cm-wysiwyg input-textarea" name="{$slider_type}[description]" id="description_{$slider_type}{$slide.slide_id|default:''}">{$slide.description|default:''}</textarea>
            </div>
        </div>
        <div class="clr button-container">
            <button type="submit" name="submit" class="submit default-button" value="submit">{if !empty($slide.slide_id)}{$lang.save}{else}{$lang.add}{/if}</button>
            {if !empty($slide) && !empty($slide.slide_id)}<button type="submit" name="submit" class="delete default-button" value="delete">{$lang.delete}</button>{/if}
        </div>
    </div>
</form>
</section>