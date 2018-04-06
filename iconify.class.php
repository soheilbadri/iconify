<?php 
/*


Copyright 2018  Soheil Badri


version 1.0

*/
class ICONIFY {

	// The directory to be iconify
	var $_dir = '';

	/////////////////////////////////////////////////////////////////////////////
	//                          Functions                                      //
	/////////////////////////////////////////////////////////////////////////////
	function __construct($dir) {
		if(isset($dir) && is_dir($dir)) {
			$this->_dir = $this->pathStandard($dir);
			return(true);
		}
		return(false);
	}

	// after making class instant
	// we just need to call run() function.
	function run() {
		// make sure desktop.ini & desktop.ico is readable and not system file
		$this->removeAttrib($this->_dir);

		// search for image in the directory
		$img = $this->getFirstImage($this->_dir);
		//echo "img: ".$img.PHP_EOL;
		//echo "<br>";
		// make optimazed transparent 256*256 png file
		$png = $this->makePng($img);
		//echo "png: ".$png.PHP_EOL;
		//echo "<br>";
		// make ico file as desktop.ico
		$ico = $this->makeIco($png);
		//echo "ico: ".$ico.PHP_EOL;
		//echo "<br>";
		// make desktop.ini file
		$this->makeDesktopini($this->_dir, $ico);
		// set attrib
		// directory +r ==> read only
		// desktop.ini & desktop.ico  => +s +h -a -r system and hidden
		$this->setAttrib($this->_dir);

		// delete png file 
		unlink($png);
	}

	// this function creates desktop.ini file into the directory
	// and fill it with proper text
	function makeDesktopini ($dir, $ico) {
	  $dir = $this->pathStandard($dir);
	  $ico = basename($ico);
	  $desktopini = $dir.DIRECTORY_SEPARATOR."desktop.ini";
	  if($desktopini = fopen($desktopini, 'w')) {
	    $text = "[.ShellClassInfo]".PHP_EOL;
	    $text .= "IconResource={$ico},0".PHP_EOL;
	    // [ViewState]\n
	    // Mode=\n
	    // Vid=\n
	    // FolderType=Generic"
	    fwrite($desktopini, $text);
	    fclose($desktopini);
	    return(true);
	  }
	  return(false);
	}

	// set directory attribute to +r read-only
	// set desktop.ini and desktop.ico file attributes to +h +s -r -a 
	// hidden system file
	function setAttrib($paths) {
	  if(is_array($paths)) {
	    foreach($paths as $path) {
	      $this->setAttrib($path);
	    }
	    return;
	  }
	  else {
	    $paths = $this->pathStandard($paths);
	    $cmd = "attrib +r {$paths}";
	    exec($cmd);
	    if(file_exists($paths.DIRECTORY_SEPARATOR."desktop.ini")) {
	      $d = $paths.DIRECTORY_SEPARATOR."desktop.ini";
	      $cmd = "attrib +h +s -r -a {$d}";
	      exec($cmd);
	    }
	    if(file_exists($paths.DIRECTORY_SEPARATOR."desktop.ico")) {
	      $d = $paths.DIRECTORY_SEPARATOR."desktop.ico";
	      $cmd = "attrib +h +s -r -a {$d}";
	      exec($cmd);
	    }
	  }
	}

	// remove h and s flag from desktop.ini and desktop.ico file 
	// so we can change the data
	function removeAttrib($paths) {
	  if(is_array($paths)) {
	    foreach($paths as $path) {
	      removeAttrib($path);
	    }
	    return;
	  }

	  if(is_dir($paths)) {
	    $paths = $this->pathStandard($paths);
	  }
	  if(file_exists($paths.DIRECTORY_SEPARATOR."desktop.ini")) {
	    $d = $paths.DIRECTORY_SEPARATOR."desktop.ini";
	    $cmd = "attrib -h -s -r -a {$d}";
	    exec($cmd);
	  }
	  if(file_exists($paths.DIRECTORY_SEPARATOR."desktop.ico")) {
	    $d = $paths.DIRECTORY_SEPARATOR."desktop.ico";
	    $cmd = "attrib -h -s -r -a {$d}";
	    exec($cmd);
	  }
	  return;
	}

	// gives an image usually a .png file and converts it to .ico file
	// using php-ico class created by Chris Jean
	// you should copy the php-ico class into yor directory
	// in the below path
	// php-ico-master/class-php-ico.php 	
	function makeIco($img) {
	  if(file_exists($img)) {
	    $p = pathinfo($img);
	    $img_ico = $p['dirname'].DIRECTORY_SEPARATOR.$p['filename'].".ico";

	    require_once('php-ico-master/class-php-ico.php');
	    $phpico = new PHP_ICO($img);
	    $phpico->save_ico($img_ico);
	    if(file_exists($img_ico)) return($img_ico);
	  }
	  return(false);
	}

	// gives an images usually the first image in the directory and
	// make an standard 256*256 png file with transparent background
	function makePng($img) {
	  if(!file_exists($img)) return(false);
	  $target = pathinfo($img);
	  $target = $target['dirname'].DIRECTORY_SEPARATOR."desktop.png";
	  $img = imagecreatefromjpeg($img);

	  // calculate dementions of image
	  $width = imagesx($img);
	  $height = imagesy($img);

	  // new dementions in 256*256 format
	  $new_width = ceil($width*256/$height);
	  $new_height = 256;

	  // scaling down image to the new dementions
	  $img = imagescale($img, $new_width, $new_height);

	  // make a blink pallet
	  $img_png = imagecreatetruecolor(256, 256);

	  // making background transparent
	  imagesavealpha($img_png, true);
	  $color = imagecolorallocatealpha($img_png, 0, 0, 0, 127);
	  imagefill($img_png, 0, 0, $color);

	  // copy the main image to the transparent 256*256 pallete
	  $desx = (256-$new_width)/2;
	  $desy = 0;
	  $srcx = 0;
	  $srcy = 0;
	  imagecopy($img_png, $img, $desx, $desy, $srcx, $srcy, $new_width, $new_height);

	  // saving image as desktop.png
	  if(imagepng($img_png, $target)) return($target);
	  return(false);
	}

	// to make all path standard 
	// I just replace every \ windows format directory separator with /
	// and remove the end / to be sure every directory dont has a / at the end
	function pathStandard ($path) {
	  str_replace("\\", "/", $path); 
	  if ( $path[strlen($path)-1] == "/" ) {
	    $path = substr($path, 0, strlen($path)-1);
	  }
	  return($path);
	}

	// Search the directory to every image file
	// just grab the first image and return it
	function getFirstImage($dir) {
	  $dir = $this->pathStandard($dir);
	  $imgExtention = array('jpg', 'jpeg','gif', 'png', 'bmp');
	  if(!is_dir($dir)) return(false);
	  $files = scandir($dir);
	  foreach ($files as $file) {
	    $file = pathinfo($file);
	    if(in_array($file['extension'], $imgExtention)) {
	      return($dir.DIRECTORY_SEPARATOR.$file['basename']);
	    }
	  }
	  return(false);
	}
}




 ?>