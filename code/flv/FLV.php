<?php

class FLV extends File 
{
	public static $allowed_file_types = array(
		'flv','avi','mov','mpeg','mpg','m4a'
	);
  
  private static $has_ffmpeg = null;
	private $allow_full_screen = true;	
	private static $ffmpeg_root = "";
	private static $termination_code;
	public static $player_count = 0;
	public static $video_width = 840;
	public static $video_height = 525;
	public static $default_thumbnail_width = 640;
	public static $default_thumbnail_height = 480;
	public static $thumbnail_folder = "video_thumbnails";
	public static $log_file_path = "dataobject_manager/code/flv/ffmpeg_log.txt";
	public static $default_popup_width = 840;
	public static $default_popup_height = 525;

	public static $thumbnail_seconds = 10;
	public static $audio_sampling_rate = 22050;
	public static $audio_bit_rate = 32;
	public static $play_button_overlay = true;
	// .gif is also available to support the IE6 world, or specify your own.
	public static $default_video_icon_path = "dataobject_manager/code/flv/images/default_video.png";
	
	public static function set_ffmpeg_root($path)
	{
		if(substr($path,-1)!="/") $path .= "/";
		self::$ffmpeg_root = $path;
	}
	
	public static function has_ffmpeg()
	{
		// Cache this so we don't have to run a shell command every time.
		if(self::$has_ffmpeg !== null) return self::$has_ffmpeg;
		
		$success = false;
		if(extension_loaded('ffmpeg'))
			$success = true;
		else {
			$output = self::ffmpeg("");
			if(self::$termination_code == 1) $success = true;
		}
		self::$has_ffmpeg = $success;
    return self::$has_ffmpeg;	
	}
	
	
	public static function echo_ffmpeg_test()
	{
		
		echo self::has_ffmpeg() ? "<span style='color:green'>FFMPEG is installed on your server and working properly. Code: ".self::$termination_code."</span>" : 
						"<span class='color:red'>FFMPEG does not appear to be installed on your server. Code: ".self::$termination_code."</span>";
	}
	
	
	protected static function ffmpeg($args)
	{
	   $descriptorspec = array(
	       0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
	       1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
	       2 => array("pipe", "w") // stderr is a file to write to
	   );
	
	   $pipes= array();
	   $cmd = self::$ffmpeg_root."ffmpeg ".$args;
	   self::log_command($cmd);	   
	   $process = proc_open($cmd, $descriptorspec, $pipes);
	
	   $output= "";
	
	   if (!is_resource($process)) return false;
	
	   #close child's input immediately
	   fclose($pipes[0]);
	
	   stream_set_blocking($pipes[1],false);
	   stream_set_blocking($pipes[2],false);
	
	   $todo= array($pipes[1],$pipes[2]);
	
	   while( true ) {
	       $read= array();
	       if( !feof($pipes[1]) ) $read[]= $pipes[1];
	       if( !feof($pipes[2]) ) $read[]= $pipes[2];
	
	       if (!$read) break;
	
	       $ready= stream_select($read, $write=NULL, $ex= NULL, 2);
	
	       if ($ready === false) {
	           break; #should never happen - something died
	       }
	
	       foreach ($read as $r) {
	           $s= fread($r,1024);
	           $output.= $s;
	       }
	   }
	
	   fclose($pipes[1]);
	   fclose($pipes[2]);
	
	   self::$termination_code = proc_close($process);
	   self::log_command($output);
	   return $output;
		
	}
	
	private static function log_command($cmd)
	{
    if(self::$log_file_path) {
      $log = Director::baseFolder()."/".self::$log_file_path;
      $f = @fopen($log, 'a');
      $entry = "[".date('Y-m-d H:i:s')."] ".$cmd."\n";
      @fwrite($f, $entry);
      @fclose($f);
    }
	}
	
	private function default_thumbnail()
	{
	   $img = new Image_Cached(self::$default_video_icon_path);
	   $img->ID = $this->ID;
	   return $img;
	}
		
	private function SWFLink()
	{
		return Director::absoluteURL('dataobject_manager/code/flv/shadowbox/libraries/mediaplayer/player.swf');
	}
	
	private function AllowFullScreen()
	{
		return $this->allow_full_screen ? "true" : "false";
	}
	
	private static function remove_file_extension($filename)
	{
		$ext = strrchr($filename, '.');  
		if($ext !== false)  
			$filename = substr($filename, 0, -strlen($ext));  
		return $filename;
	}
	
	private static function clean_file($str)
	{
		$t = strtolower($str);
		$t = str_replace('&amp;','-and-',$t);
		$t = str_replace('&','-and-',$t);
		$t = ereg_replace('[^A-Za-z0-9]+','-',$t);
		$t = ereg_replace('-+','-',$t);
		return $t;
	}
	
	public function Icon()
	{
		return SAPPHIRE_DIR."/images/app_icons/mov_32.gif";
	}
	
	public function FLVPath()
	{
		return self::remove_file_extension($this->Filename).".flv";		
	}
	
	public function FLVLink()
	{
		return Director::absoluteURL($this->FLVPath());
	}
	
	private function absoluteRawVideoLink()
	{
		return Director::baseFolder()."/".$this->Filename;	
	}
	
	private function absoluteFLVPath()
	{
		return Director::baseFolder()."/".$this->FLVPath();
	}
	
	private function hasFLV()
	{
		return Director::fileExists($this->FLVPath());
	}
	
	public function getThumbnail()
	{
	 if($img = DataObject::get_one("Image","\"Title\" = 'flv_thumb_{$this->ID}'"))
	   return Director::fileExists($img->Filename) ? $img : false;
	 return false;
	}
	
	private function createFLV()
	{
		$args = sprintf("-i %s -ar %d -ab %d -f flv %s",
			$this->absoluteRawVideoLink(),
			self::$audio_sampling_rate,
			self::$audio_bit_rate,
			$this->absoluteFLVPath()
		);
		
		$output = self::ffmpeg($args);	
	}
	
	private function createThumbnail()
	{
      $img_title = "flv_thumb_".$this->ID;
      if($existing = DataObject::get("Image","\"Title\" = '$img_title'")) {
        foreach($existing as $file) $file->delete();
      }
			$folder = Folder::findOrMake(self::$thumbnail_folder);
			$img_filename = self::clean_file(self::remove_file_extension($this->Title)).".jpg";
			$abs_thumb = Director::baseFolder()."/".$folder->Filename.$img_filename;
			$args = sprintf("-y -i %s -an -s %s -ss %d -an -r 1 -vframes 1 -y -vcodec mjpeg -f mjpeg %s",
				$this->absoluteFLVPath(),
				self::$default_thumbnail_width."x".self::$default_thumbnail_height,
				self::$thumbnail_seconds,
				$abs_thumb
			);
			self::ffmpeg($args);	

			$img = new Image();
			$img->setField('ParentID',$folder->ID);
			$img->Filename = $folder->Filename.$img_filename;
			$img->Title = $img_title;
			$img->write();
	}
	
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if(!$this->hasFLV())
			$this->createFLV();
		if(!$this->getThumbnail())
		  $this->createThumbnail();
	}
	
	
	public function Player($width = null, $height = null)
	{
		if($width === null) $width = self::$video_width;
		if($height === null) $height = self::$video_height;
		$image = ($thumb = $this->VideoThumbnail()) ? $thumb->CroppedImage($width,$height)->URL : "";
		self::$player_count++;
		Requirements::javascript('dataobject_manager/code/flv/swfobject.js');
		Requirements::customScript(sprintf(
				"swfobject.embedSWF('%s','player-%s','%d','%d','9.0.0','expressInstall.swf',{file : '%s',image : '%s'},{allowscriptaccess : 'true', allowfullscreen : '%s'})",
				$this->SWFLink(),
				self::$player_count,
				$width,
				$height,
				$this->FLVLink(),
				$image,
				$this->AllowFullScreen()
			)
		);
		return "<div id='player-".self::$player_count."'>Loading...</div>";
	}
	
	
	public function forTemplate()
	{
		return $this->Player();
	}
	
	public function VideoThumbnail()
	{
	  if(self::has_ffmpeg() && !$img = $this->getThumbnail())
	    $this->createThumbnail();
    $img = $this->getThumbnail();
	  return $img ? $img : $this->default_thumbnail();
	  
	}
	
	/**
	 * SSViewer doesn't accept more than two arguments for template
	 * functions. Here's a hack. If an arg is, e.g. 200x400 it will
	 * split that into width/height for thumb for first arg, and popup
	 * for second arg.
	 *
	 * Examples: 
	 * $VideoPopup(450,200) : Returns a video popup with thumbnail 
	 *                        450 width, 200 height. Popup is default dimensions
	 *
	 * $VideoPopup(450x200,800x600) : Returns a video popup with thumbnail
	 *                                450 width, 200 height. Popup is 800 width, 600 height.
	 *
	 * $VideoPopup(450x200) : Same as first example.
	 *
	 */ 
	public function VideoPopup($arg1 = null, $arg2 = null)
	{
		$popup_width = null;
		$popup_height = null;
		if($arg1 !== null && stristr($arg1,"x"))
		  list($thumb_width,$thumb_height) = explode("x",$arg1);
		else
		  $thumb_width = $arg1;
		
		if($arg2 !== null && stristr($arg2,"x"))
		  list($popup_width,$popup_height) = explode("x",$arg2);
		else
		  $thumb_height = $arg2;
		  
		if($popup_width === null) $popup_width = self::$default_popup_width;
		if($popup_height === null) $popup_height = self::$default_popup_height;
		
		return $this->customise(array(
			'PopupWidth' => $popup_width,
			'PopupHeight' => $popup_height,
			'ThumbWidth' => $thumb_width,
			'ThumbHeight' => $thumb_height,
			'Title' => $this->Title,
			'Link' => $this->FLVLink(),
			'Thumbnail' => $this->VideoThumbnail()->CroppedImage($thumb_width, $thumb_height),
			'PlayButton' => self::$play_button_overlay
		))->renderWith(array('FLVpopup'));
		
	}
}


?>