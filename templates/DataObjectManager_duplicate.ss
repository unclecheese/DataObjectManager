<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<% base_tag %>
    <meta content="text/html; charset=utf-8" http-equiv="Content-type"/> 		
	</head>
	<body class="DataObjectManager-popup loading 
		<% if String %>
			<% if NestedController %>nestedController<% end_if %>
		<% else %>
			<% if DetailForm.NestedController %>nestedController<% end_if %>
		<% end_if %>
	">
		<div class="right $PopupClasses">
			<h2><% _t('DataObjectManager.DUPLICATE','Duplicate') %> $SingleTitle</h2>
			$DuplicateForm
		</div>
	</body>
</html>
