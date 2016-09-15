<?php
/**
 * @author Piotr Podstawski <piotr.podstawski@gammanet.pl>
 */

 
class Image
{
    private $source=null;
    private $src_w,$src_h,$ext;
    
    public function __construct($img)
    {
        if (file_exists($img) && strlen($img) > 0) {
            $pinfo = pathinfo($img);
            $this->ext = strtolower($pinfo['extension']);

            switch ($this->ext) {
                case 'jpg':
                case 'jpeg':
                    $this->source = imagecreatefromjpeg($img);
                    break;

                case 'png':
                    $this->source = imagecreatefrompng($img);
                    $background = imagecolorallocate($this->source, 0, 0, 0);
                    imagecolortransparent($this->source, $background);
                    imagealphablending($this->source, false);
                    imagesavealpha($this->source, true);
                    break;

                case 'gif':
                    $this->source = imagecreatefromgif($img);
                    $background = imagecolorallocate($this->source, 0, 0, 0);
                    imagecolortransparent($this->source, $background);
                    imagealphablending($this->source, false);
                    imagesavealpha($this->source, true);
                    break;
                
                default:
                    return false;
            }
            
            
            list ($this->src_w, $this->src_h) = getimagesize($img);
           
            
            
        }
         
        
    }
    
    
    
    public function min($dst, $dst_w, $dst_h, $scale = false, $crop = false)
    {
        
        if (!$this->source) return false;
        $ext=end(explode('.',strtolower($dst)));
        
        
        if ($dst_w && !$dst_h) $dst_h = round(($dst_w*$this->src_h)/$this->src_w);
        if ($dst_h && !$dst_w) $dst_w = round(($dst_h*$this->src_w)/$this->src_h);
        
        if (!$dst_w || !$dst_h) return false;
        
        
    
        if ($crop) {
    
            $t = $this->image_crop_calc($this->src_w, $this->src_h, $dst_w, $dst_h);
            $thumb = $this->createimage($ext, $dst_w, $dst_h);
            imagecopyresampled($thumb, $this->source, 0, 0, $t['x'], $t['y'], $dst_w, $dst_h, $t['w'], $t['h']);
        } else {
            if ($scale) {
                $t = $this->image_scale_calc($this->src_w, $this->src_h, $dst_w, $dst_h);
                $thumb = $this->createimage($ext, $t['x'], $t['y']);
                imagecopyresampled($thumb, $this->source, 0, 0, 0, 0, $t['x'], $t['y'], $this->src_w, $this->src_h);
                $dst_w=$t['x'];
                $dst_h=$t['y'];
            } else {
                $thumb = $this->createimage($ext, $dst_w, $dst_h);
                imagecopyresampled($thumb, $this->source, 0, 0, 0, 0, $dst_w, $dst_h, $this->src_w, $this->src_h);
            }
        }
        
        if (file_exists($dst)) {
            if (($imagesize = getimagesize($dst)) !== false) {
                if ($imagesize[0]==$dst_w && $imagesize[1]==$dst_h) return $dst;
            }
        }
    
        $img_config=Bootstrap::$main->getConfig('img');
        $q=isset($img_config['jpeg']['quality'])?$img_config['jpeg']['quality']:80;
    
        
        
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($thumb, $dst, $q);
                break;
    
            case 'png':
                imagepng($thumb, $dst);
        
                if (isset($img_config['png2jpeg']) && $img_config['png2jpeg']) {
                    $tmp=sys_get_temp_dir().'/'.md5($dst);
                    imagejpeg($thumb, $tmp, $q);
                    if (filesize($tmp)<filesize($dst)) {
                        rename($tmp,$dst);
                    } else {
                        unlink($tmp);
                    }
                }
                break;
    
            case 'gif':
                imagegif($thumb, $dst);
                break;
        }
        
        
        
        //imagepng($this->source, $dst);
        //$dst2=str_replace('Ala','Ela',$dst); rename($dst,$dst2); die('<img src="http://piotr.webkameleon.com/kameleon/public/uimages/49/1/widgets/gallery2/gfx/icon/usg/Ela.png?a='.time().'"/>');
        
        return $dst;        
        
    }
    
    
    
    
    private function image_scale_calc($src_width, $src_height, $dst_width, $dst_height)
    {
        $ratio = $src_width / $src_height;

        if ($dst_width / $dst_height > $ratio) {
            $dst_width = $dst_height * $ratio;
        } else {
            $dst_height = $dst_width / $ratio;
        }

        return array('x' => round($dst_width), 'y' => round($dst_height));
    }

    private function image_crop_calc($src_width, $src_height, $dst_width, $dst_height)
    {
        $ret=array('x'=>0,'y'=>0,'h'=>0,'w'=>0);
        if (!$src_width || !$dst_width || !$src_height || !$dst_height) return $ret;
        
        if ($src_height / $src_width > $dst_height / $dst_width) {
            $ret['x'] = 0;
            $ret['y'] = round(($src_height - ($dst_height * $src_width) / $dst_width) / 2);
            $ret['w'] = $src_width;
            $ret['h'] = round(($src_width * $dst_height) / $dst_width);

        } else {
            $ret['x'] = round(($src_width - ($dst_width * $src_height) / $dst_height) / 2);
            $ret['y'] = 0;
            $ret['w'] = round(($src_height * $dst_width) / $dst_height);
            $ret['h'] = $src_height;
        }

        return $ret;
    }
    
    private function createimage($ext,$w,$h)
    {
        if (!$w || !$h) return null;
        
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return imagecreatetruecolor($w,$h);

            case 'png':
            case 'gif':
                $newImg=imagecreatetruecolor($w,$h);                   
                

                
                //$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
                //imagefilledrectangle($newImg, 0, 0, $w, $h, $transparent);
                
                $background = imagecolorallocate($newImg, 0, 0, 0);
                imagecolortransparent($newImg, $background);
                
                imagealphablending($newImg, false);
                imagesavealpha($newImg,true); 
                
                /*
                
                imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
                imagealphablending($newImg, false);
                imagesavealpha($newImg, true);
                
                */
                
                return $newImg;
                
                //return imagecreate($w,$h);
        }        
    }
    
    
    
    
}
   