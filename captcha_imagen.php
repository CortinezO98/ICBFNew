<?php 
	header("Content-type: image/png");
	// error_reporting(E_ALL);
	// ini_set('display_errors', '1');
	$str="";
	$string = "abcjstxyz2ABCDEFGH345defgh67kmnVWXpqr89IJKLMNPQuvwRSTUYZ"; 
	for($i=0;$i<5;$i++){
	    $pos = rand(0,55);
	    $str .= $string[$pos];
	}
	$img_handle = ImageCreate(65, 45) or die ("Es imposible crear la imagen");
	$back_color = ImageColorAllocate($img_handle, 114, 191, 68);
	$txt_color = ImageColorAllocate($img_handle, 255 , 255, 255);
	ImageString($img_handle, 5, 10, 15, $str, $txt_color);
	Imagepng($img_handle);
	setcookie('captcha', sha1($str), time()+60*3);
?>