<?php

class MP3 extends File 
{
	static $allowed_file_types = array (
		'mp3'
	);

	private static $player_count = 0;
		
	public function Player()
	{
		self::$player_count++;
		return $this->customise(array(
			'SWFLink' => Director::absoluteURL('dataobject_manager/code/mp3/player.swf'),
			'MP3Link' => Director::absoluteURL($this->URL),
			'Count' => self::$player_count
		))->renderWith(array('mp3'));
	}
	
	public function forTemplate()
	{
		return $this->Player();
	}
	
	
}


?>