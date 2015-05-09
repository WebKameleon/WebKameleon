<?php
class thumbController extends mediaController {
    
    protected function getLocalPath() {

        $path=explode('/',$this->fullpath);
        
        
        if (count($path)==2)
        {
            $template=$path[1];
            $file='.thumbnail';
        }
        elseif(count($path)==3)
        {
            $template=$path[1];
            $file='.'.$path[2];            
        }
        else return;
        
        $token='thumb'.$file.'-'.$template;
        
        
        $img=Tools::cache($token);
        
        if (!$img)
        {
            $server = new serverModel();
            
            
            if (abs($template+0)>0)
            {
                $template=$server->get($template);
            } elseif ($template && $template!='default')
            {
                $template=base64_decode($template);
                if (substr($template,0,6)=='media/')
                {
                    $template = $server->find_one_by_nazwa(substr($template,6));
                }            
            }
            
            
 
            $img=Tools::cache($token,Tools::get_template_thumb($template,$file,true));
            
            if (!$img) $img=Tools::cache($token,Tools::get_template_thumb($template,'.thumbnail',true));
            
            
        }
        
        
        return $img;

    }
    
}
