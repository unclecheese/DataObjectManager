<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<% base_tag %>
    <meta content="text/html; charset=utf-8" http-equiv="Content-type"/> 
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />		
	</head>
	<body class="DataObjectManager-popup loading <% if String %><% if NestedController %>nestedController<% end_if %><% else %><% if DetailForm.NestedController %>nestedController<% end_if %><% end_if %>	">
		<div class="right $PopupClasses">
			$DetailForm
		</div>
		<% if HasPagination %>
		<div id="pagination">
			<% if PrevRecordLink %>
				<div class="prev"><a href="$PrevRecordLink" title=<% _t('PREVIOUS','Previous') %>">&laquo;<% _t('PREVIOUS','Previous') %></a></div>
			<% end_if %>
			<% if NextRecordLink %>
				<div class="next"><a href="$NextRecordLink" title=<% _t('NEXT','Next') %>"><% _t('NEXT','Next') %>&raquo;</a></div>
			<% end_if %>
		</div>
		<% end_if %>
	</body>
</html>
