<% require css(dataobject_manager/code/flv/shadowbox/shadowbox.css) %>
<% require javascript(sapphire/thirdparty/jquery/jquery.js) %>
<% require javascript(dataobject_manager/code/flv/shadowbox/shadowbox.js) %>
<% require javascript(dataobject_manager/code/flv/shadowbox_init.js) %>
<% require css(dataobject_manager/code/flv/css/flv.css) %>

<a rel="shadowbox;width={$PopupWidth};height={$PopupHeight}" title="$Title" href="$Link" class="flv-popup" style="width:{$ThumbWidth}px;height:{$ThumbHeight}px">
	<% if PlayButton %><span>Play</span><% end_if %>
	$Thumbnail
</a>		