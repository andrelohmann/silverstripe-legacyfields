<ul id="$ID" class="$extraClass">
	<% loop Options %>
		<li class="$Class">
			<input id="$ID" class="radio" name="$Name" type="radio" value="$Value"<% if isChecked %> checked<% end_if %><% if isDisabled %> disabled<% end_if %> />
			<label for="$ID"><% if $Title.CMSThumbnail %>$Title.CMSThumbnail<% else %>$Title<% end_if %></label>
		</li>
	<% end_loop %>
</ul>
