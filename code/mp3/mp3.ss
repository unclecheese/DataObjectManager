<% require javascript(dataobject_manager/code/mp3/audio-player.js) %>

<object type="application/x-shockwave-flash" data="$SWFLink" id="audioplayer1" height="24" width="290">
	<param name="movie" value="$SWFLink">
	<param name="FlashVars" value="playerID={$Count}&amp;soundFile={$MP3Link}">
	<param name="quality" value="high">
	<param name="menu" value="false">
	<param name="wmode" value="transparent">
</object>