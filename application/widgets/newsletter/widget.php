<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class newsletterWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'newsletter';

    
    public function edit()
    {
        $page=isset($_GET['page'])?$_GET['page']:$this->webpage['id'];
        
        $this->check_scope('newsletter',$page);

        
        return parent::edit();
    }
    
    public function delete()
    {
        $observersent= new observersentModel();
        $observersent->removeAllForEvent('kmw-newsletter-'.$this->webtd['sid']);
        return parent::delete();
    }


    public function run()
    {
        $user=new userModel();
        $user->getCurrent();
        $this->data['user'] = $user->data();

        $this->data['oauth2'] = Bootstrap::$main->getConfig('oauth2');        
        $access_token=json_decode(Google::getUserClient(null,false,'newsletter')->getAccessToken());
        $this->data['access_token']=$access_token->access_token;


        if (isset($this->data['a']))
        {

            $client=Google::getUserClient(null,false,'newsletter');
            $service = Google::getUrlshortenerService($client);

            $this->data['links'] = $this->data['a'];
            $webpage=new webpageModel();
            
            
            foreach ($this->data['links'] AS $href=>$goo)
            {
                $href2=$href;
                if (substr($href2,0,13)=='kameleon:link')
                {
                    $href2=substr($href2,14,strlen($href2)-15);
                    $href2=explode(',',$href2);
                
                    $webpage->getOne($href2[0]);
                    $href2=$webpage->title;
                    
                    
                }
                
                if ($goo)
                {
                    //$goo_gl=$service->url->get($goo);
                    $goo_gl=end(explode('/',$goo));
                }
                else
                {
                    $goo=null;
                }
                
                $this->data['links'][$href]=array('href'=>$href2,'goo'=>$goo_gl);
                
            }
            //mydie($this->data['links']);
        }


        
        return parent::run();
    }
    
    
    public function update()
    {
        $plain=$this->webtd['plain'];
        
        $imgs=array();
        while ($pos=strpos($plain,UIMAGES_TOKEN))
        {
            $plain=substr($plain,$pos+strlen(UIMAGES_TOKEN)+1);
            
            $pos_1=strpos($plain,'"');
            $pos_2=strpos($plain,"'");
            
            if ($pos_1 && $pos_2) $pos=min($pos_1,$pos_2);
            elseif ($pos_1) $pos=$pos_1;
            else $pos=$pos_2;
            
            
            if ($pos) $imgs[]=substr($plain,0,$pos);
            
        }
        
        if (!isset($this->data['imgs'])) $this->data['imgs'] = array(); 
        
        $uimages=Bootstrap::$main->session('uimages_path');
        foreach($imgs AS $img)
        {
            if (!file_exists("$uimages/$img")) continue;
            
            $mtime=filemtime("$uimages/$img");
            
            if (isset($this->data['imgs'][$img]) && strtotime($this->data['imgs'][$img]['mediaCreatedTime']) >= $mtime) continue;
            
            $this->data['imgs'][$img]=Gplus::upload_img("$uimages/$img");
            
            
        }
        
        $plain=$this->webtd['plain'];
        
        $links=array();
        
        
        while (($pos=strpos(strtolower($plain),'<a '))!==false)
        {
            $plain=substr($plain,$pos+2);
            
            $pos=strpos(strtolower($plain),'href=');
            $posend=strpos(strtolower($plain),'>');
            
            if ($pos+5+2 >= $posend) continue;
            $plain = substr($plain,$pos+5);
            
            $quote=$plain[0];
            $plain=substr($plain,1);
            $pos=strpos($plain,$quote);
            
            $a=substr($plain,0,$pos);
            
            if (!isset($this->data['a'][$a])) $this->data['a'][$a]='';
            
        }
        
        

        
        return parent::update();
    }

}
