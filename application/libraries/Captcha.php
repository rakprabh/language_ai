<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Captcha{	
	
	public function __construct(){	
		

	}
	
	public function load(){
		
		session_start();
		$code=rand(1000,9999);
		$_SESSION["code"]=$code;
		$im = imagecreatetruecolor(50, 24);
		$bg = imagecolorallocate($im, 22, 86, 165); //background color blue
		$fg = imagecolorallocate($im, 255, 255, 255);//text color white
		imagefill($im, 0, 0, $bg);
		imagestring($im, 5, 5, 5,  $code, $fg);
		header("Cache-Control: no-cache, must-revalidate");
		header('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
		
	}
	
	public function validate($captcha_text){
		
		session_start();
		
		if(!(isset($captcha_text)&& $captcha_text!=""&&$_SESSION["code"]==$captcha_text))
		{
			die("Wrong Captcha Entered");
		}
	}
}