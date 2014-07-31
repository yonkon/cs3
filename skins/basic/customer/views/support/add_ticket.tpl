<div id="main_content">
    <h1>{$lang.Support}</h1>
    <h2>{$lang.ticket_to_support}</h2>
    <span>{$lang.text_support_header}</span>
    <form method="post" action="{'support.add_ticket'|fn_url}">
    <label for="question_type">{$lang.question_type}</label> <select  id="question_type" name="question_type">
        <option value="q">{$lang.select_question_type}</option>
        <option value="p">{$lang.agent_not_paid}</option>
        <option value="q">{$lang.question}</option>
        <option value="t">{$lang.question_technical}</option>
    </select>
    <label for="theme">{$lang.theme}</label><input id="theme" name="theme">
    <label for="message">{$lang.message}</label><textarea id="message" name="message"></textarea>
    <img src=""><input type="file" name="application" title="{$lang.upload_file}"><span>{$lang.upload_help_text_big}</span>
    <span>{$lang.upload_format_help_text}</span>
    <button type="submit" name="submit" value="{$lang.submit}">{$lang.send}</button>
    </form>
</div>
