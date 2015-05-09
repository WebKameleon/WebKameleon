<?php
class imagesController extends mediaController {
    
    protected function getLocalPath() {
        $template = Bootstrap::$main->session('template');   
        if (!$template) return null;
        
        
        
        $path=$template.'/'.$this->fullpath;
        if (!file_exists($path)) {
            if (strstr($this->fullpath,'/widgets/')) {
                $template=WIDGETS_PATH;
                
                $path = WIDGETS_PATH.'/'.preg_replace('#images/widgets/([^/]+)#','\\1/images',$this->fullpath);

            }
        }
        
        return $path;
    }
    
}
