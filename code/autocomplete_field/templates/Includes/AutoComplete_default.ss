<% if Results %>
	<ul>
	<% control Results %>
		<li>
			<h5><a href="$Link">$AutoCompleteTitle</a></h5>
			<div>$AutoCompleteSummary</div>
		</li>
	<% end_control %>
	</ul>
<% end_if %>