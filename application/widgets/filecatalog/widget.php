<?php

class filecatalogWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'filecatalog';

    
    public function edit()
    {
        parent::edit();
        
        $this->files=array();
        
        if (isset($this->data['files']))
        {
            foreach ($this->data['files'] AS $key=>$name)
            {
                $id=explode(':',$key);
                $this->files[]=array(
                    'key'=>$key,
                    'id'=>str_replace('=','0',$id[1]),
                    'rel'=>$id[1],
                    'mime'=>$id[0],
                    'fname'=>$name
                );
                
            }
        }

        
    }
    
    
    public function run()
    {    
        parent::run();
        
        $session=Bootstrap::$main->session();
        $this->files=array();
        if (isset($this->data['files'])) foreach ($this->data['files'] AS $k=>$name)
        {
            $k=explode(':',$k);
            $mime=$k[0];
            $path=base64_decode($k[1]);
            
            echo "$mime: $path<br>";
            if ($mime=='directory')
            {
                $files=$this->browse($path,$session['ufiles_path']);
                foreach ($files AS $k=>$f)
                {
                    if (!isset($this->files[$k]))
                    {
                        $this->files[$k]=array('name'=>$f,'url'=>$session['ufiles'].$k,'type'=>end(explode('.',$f)));
                    }
                }
            }
            else
            {
                $this->files[$path]=array('name'=>$name,'url'=>$session['ufiles'].$path,'type'=>end(explode('-',$mime)));
            }
            
        }
        
    }
    
    private function browse($path,$dir)
    {
        $ret=array();
        if (!file_exists($dir.$path)) return $ret;
        
        foreach (scandir($dir.$path) AS $file)
        {
            if ($file[0]=='.') continue;
            if (is_dir($dir.$path.'/'.$file)) $ret=array_merge($ret,$this->browse($path.'/'.$file,$dir));
            else $ret[$path.'/'.$file]=$file;
        }
        
        return $ret;
    }
    
}
