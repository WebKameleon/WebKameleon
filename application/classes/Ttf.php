<?php
// copyright Gammanet Sp. z o.o.
// @author Michał Skoraszewski

class Ttf {
	
	private $_filename = ""; // założenie, coś w stylu "link501_234.png";
	private $_uimages = ""; // założenie, coś w stylu "/uimages/2/1/"
	private $_directory = ""; // założenie, coś w stylu "katalog/1/" 
	private $_transparency = false;
	private $_bg_color = "#000000";
	private $_crop = false;
	private $_width = 500;
	private $_height = 100;
	// OBRAZEK W TLE
	private $_img_src = "";
	private $_img_pos_x = 0;
	private $_img_pos_y = 0;
	// NAPIS
	private $_txt_font = "segoesc";
	private $_txt_size = 14;
	private $_txt_color = "#000000";
	private $_txt_pos_x = 0;
	private $_txt_pos_y = 0;
	private $_txt_text = "";
	private $_txt_wrap = 0;
	private $_txt_wrap_at = "";
	private $_error = "";
	
	public function __construct($uimages,$dir,$width=-1,$height=-1){
		$this->_uimages = $uimages;
		$this->_directory = $dir;
		if ((int)$width>0) $this->_width=(int)$width;
		if ((int)$height>0) $this->_height=(int)$height;
		
		if (!function_exists('imagepng')) {
			$this->_error = "Brak GD GIF library";
			return false;
		}
		if (!function_exists('imagettftext')) {
			$this->_error = "Brak FreeType library";
			return false;
		}
		
	
		return true;
	}
	
	public function Transparency(){
		$this->_transparency = true;
	}
	
	public function Crop(){
		$this->_crop = true;
	}
	
	public function BgColor($val){
		$this->_bg_color = $val;
	}
	
	public function AddText($text, $size=-1, $font="", $pos_x=-1, $pos_y=-1, $color="", $wrap=-1){
		$this->_txt_text = $text; 
		if ((int)$size>=0) $this->_txt_size = (int)$size;
		if (strlen($font)>0 && file_exists(__DIR__."/fonts/".$font.".ttf")) $this->_txt_font = $font;
		if ((int)$pos_x>=0) $this->_txt_pos_x = (int)$pos_x;
		if ((int)$pos_y>=0) $this->_txt_pos_y = (int)$pos_y;
		if (strlen($color)>0) $this->_txt_color = $color;
		if ((int)$wrap>=0) $this->_txt_wrap = (int)$wrap;
	}
	
	public function AddImg($src, $pos_x=-1, $pos_y=-1){
		if (file_exists($this->_uimages.$src)) $this->_img_src = $src;
		if ((int)$pos_x>=0) $this->_img_pos_x = (int)$pos_x;
		if ((int)$pos_y>=0) $this->_img_pos_y = (int)$pos_y;
	}
	
	public function Wrap($val){
		$this->_txt_wrap = (int)$val;
	}
	
	public function WrapAt($val){
		$this->_txt_wrap_at = $val;
	}
	
	private function WrapText($str,$num) {
        $strs = explode(' ',$str);
        $strlenMax = $num;

        foreach($strs as $item) {
            $strlenTemp += strlen(utf8_decode($item));
            $tempStr .= $item." ";
            if($strlenTemp>$strlenMax) {
                $ret .= $tempStr."\n";
                $strlenTemp =0;
                $tempStr = '';
            }
        }
        if($strlenTemp<=$strlenMax) {
            $ret .= $tempStr;
        }
        return $ret;
    }
	
	public function PLttf ($text) 
	{ 
		$znaki = Array ( 
		  "ą"=>"&#261;", 
		  "Ą"=>"&#260;", 
		  "ę"=>"&#281;", 
		  "Ę"=>"&#280;", 
		  "ł"=>"&#322;", 
		  "Ł"=>"&#321;", 
		  "Ń"=>"&#323;", 
		  "ń"=>"&#324;", 
		  "Ś"=>"&#346;", 
		  "ś"=>"&#347;", 
		  "Ź"=>"&#377;", 
		  "ź"=>"&#378;", 
		  "Ż"=>"&#379;", 
		  "ż"=>"&#380;", 
		  "Ć"=>"&#262;", 
		  "ć"=>"&#263;",
		  "Ó"=>"&#211;",
		  "ó"=>"&#243;"
		  ); 
		return strtr($text,$znaki); 
	}
	
	public function Save($filename){
		$this->_filename = $filename;
		$new_x = $this->_width;
		$new_y = $this->_height;
		

		if (strlen($this->_txt_text)>0)
		{
			$colors = sscanf("#".str_replace("#","",$this->_txt_color), '#%2x%2x%2x');
			
			if ($this->_txt_wrap>0) $this->_txt_text = $this->WrapText($this->_txt_text, $this->_txt_wrap);
			if (strlen($this->_txt_wrap_at)>0) $this->_txt_text = str_replace($this->_txt_wrap_at,"\n",$this->_txt_text);
			
			$txtimg = imagecreatetruecolor(980,1600);
			imagesavealpha($txtimg, true);
			imagealphablending($txtimg, false);
			$background = imagecolorallocatealpha($txtimg, 255, 255, 255, 127);
			imagefilledrectangle($txtimg, 0, 0, 980, 1600, $background);
			imagealphablending($txtimg, true);
			$rgb = str_split(ltrim($this->_txt_color,'#'),2);
			$colors = imagecolorallocatealpha($txtimg,hexdec($rgb[0]),hexdec($rgb[1]),hexdec($rgb[2]),0); 
			$sizebox = imagettftext($txtimg, $this->_txt_size, 0, 0, $this->_txt_size, $colors, APPLICATION_PATH."/fonts/".$this->_txt_font.".ttf", $this->PLttf($this->_txt_text));
			// bottom
			if ($this->_crop)
			{		
				$new_x = $sizebox[2];
				$new_y = $sizebox[3];
				$new_x += $this->_txt_pos_x;
				$new_y += $this->_txt_pos_y;
			}
		}
		else
		{
			$new_x = 0;
			$new_y = 0;
		}
		//$new_x=455;
		//$new_y=120;
		
		
		if (strlen($this->_img_src)>0)
		{
			list($xx,$yy)=getimagesize($this->_uimages.$this->_img_src);
			if ($new_x<$xx+$this->_img_pos_x) $new_x=$xx + $this->_img_pos_x;
			if ($new_y<$yy+$this->_img_pos_y) $new_y=$yy + $this->_img_pos_y;
		}
		$this->_width = $new_x;
		$this->_height = $new_y;
		$image_p = imagecreatetruecolor($this->_width, $this->_height);
		if ($this->_transparency)
		{
			imagesavealpha($image_p,true);
	        imagealphablending($image_p, false);
	        $background = imagecolorallocatealpha($image_p,255,255,255,127);
			imagefilledrectangle($image_p,0,0,$this->_width, $this->_height,$background);
			imagealphablending($image_p, true);	
		}
		else
		{
			$colors = sscanf("#".str_replace("#","",$this->_bg_color), '#%2x%2x%2x');
			$background = imagecolorallocate($image_p,$colors[0],$colors[1],$colors[2]);
			imagefilledrectangle($image_p,0,0,$this->_width, $this->_height,$background);
		}
		
		if (strlen($this->_img_src)>0)
		{
			$fileinfo = pathinfo($this->_uimages.$this->_img_src);
			$ext = strtolower($fileinfo['extension']);
			list($xx,$yy)=getimagesize($this->_uimages.$this->_img_src);
			
			switch ($ext) {
				case 'jpg':
				case 'jpeg':
					$img = imagecreatefromjpeg($this->_uimages.$this->_img_src);
					break;
					
				case 'gif':
					$img = imagecreatefromgif($this->_uimages.$this->_img_src);
					break;
					
				case 'png':
					$img = imagecreatefrompng($this->_uimages.$this->_img_src);
					break;
					
				case 'bmp':
					$img = imagecreatefromwbmp($this->_uimages.$this->_img_src);
					break;
			}
			
			if ($img)
			{
				imagecopy($image_p, $img, $this->_img_pos_x, $this->_img_pos_y, 0, 0, $xx, $yy);
			}
		}
		
		if ($txtimg)
		{
			imagecopy($image_p, $txtimg, $this->_txt_pos_x, $this->_txt_pos_y, 0, 0, $new_x, $new_y);
		}

		if (imagepng($image_p,$this->_uimages.$this->_directory.$this->_filename)) $ok=true;
		else $ok=false;
		@imagedestroy($image_p);
		@imagedestroy($txtimg);
		@imagedestroy($img);
		return $ok;
	}
}
