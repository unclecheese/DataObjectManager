<% if Results %>
	<ul class="livedropdown_results">
	<% control Results %>
		<li>
			<a href="#{$Key}">$Val</a>
		</li>
	<% end_control %>
	</ul>
<% end_if %>