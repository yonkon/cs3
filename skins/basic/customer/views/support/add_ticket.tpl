<div id="main_content">
    <h1 class="h1">{$lang.Support}</h1>
    <h2 class="h2">{$lang.ticket_to_support}</h2>
    <span style="margin-bottom: 15px; display: inline-block;">{$lang.text_support_header}</span>
    <form method="post" action="{'support.add_ticket'|fn_url}" enctype="multipart/form-data">
   <div class="input_group"><label for="question_type">{$lang.question_type}</label> <select  id="question_type" name="question_type">
        <option value="q">{$lang.select_question_type}</option>
        <option value="p">{$lang.agent_not_paid}</option>
        <option value="q">{$lang.question}</option>
        <option value="t">{$lang.question_technical}</option>
    </select></div>
    <div class="input_group"> <label for="theme">{$lang.theme}</label><input id="theme" name="theme"></div>
    <div class="input_group"><label for="message">{$lang.message}</label><textarea id="message" name="message"></textarea></div>
    <div id="upload_file">
        <img src="">
        <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
        <input type="file" name="application" title="{$lang.upload_file}">
        <div id="text_upload"><span>{$lang.upload_help_text_big}</span></div>
        <div id="text_upload_format"><span>{$lang.upload_format_help_text}</span></div>
        <div id="button_submit_upload"><button type="submit" name="submit" value="{$lang.submit}">{$lang.send}</button></div>
    </div>
    </form>
</div>
