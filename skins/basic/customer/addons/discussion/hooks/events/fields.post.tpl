<div class="form-field">
	<label for="elm_dsc" class="cm-required">{$lang.discussion_title_giftreg}:</label>
	{assign var="discussion" value=$event_data.event_id|fn_get_discussion:"G"}
	<select id="elm_dsc" name="event_data[discussion_type]">
		<option {if $discussion.type == "D"}selected="selected"{/if} value="D">{$lang.disabled}</option>
		<option {if $discussion.type == "C"}selected="selected"{/if} value="C">{$lang.enabled}</option>
	</select>
</div>