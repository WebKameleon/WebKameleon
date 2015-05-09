<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class gdriveWidget extends Widget
{
    public $name = 'gdrive';
    
    public function edit()
    {
        $this->check_scope('drive',$_GET['page']);  
        
        $this->data['oauth2'] = Bootstrap::$main->getConfig('oauth2');        
        $access_token=json_decode(Google::getUserClient(null,false,'drive')->getAccessToken());
        
        $this->data['access_token']=$access_token->access_token;
        
        return parent::edit();
    }
    
    
    public function update()
    {
        if (isset($this->data['id']) && $this->data['id']) {
        
            $client=Google::getUserClient(null,false,'drive');
            $service = Google::getDriveService($client);
            $file=$service->files->get($this->data['id']);
            
            
            $need_to_publish = true;
            $need_to_share = false;
            

            
            switch ($file['mimeType'])
            {
                case 'application/vnd.google-apps.drawing':
                    $url=$file['embedLink'];
                    $_url=explode('?',$url);                    
                    $url=preg_replace('~/preview$~','/pub',$_url[0]);
                    $this->data['width']=str_replace('%','',$this->data['width']);
                    $url.='?w='.$this->data['width'];
                    break;
                
                case 'application/vnd.google-apps.document':
                    $url=$file['embedLink'];
                    $_url=explode('?',$url);                    
                    $url=preg_replace('~/preview$~','/pub',$_url[0]);
                    break;
                
                case 'application/vnd.google-apps.spreadsheet':
                    $url=str_replace('spreadsheet/ccc','spreadsheet/pub',$file['embedLink']);
                    $url=str_replace('&chrome=false&','&',$url);
                    $url=str_replace('/htmlembed','/pubhtml',$url);
                    if (strstr($url,'/pubhtml')) $url.='?widget=true&amp;headers=false';
                    
                    break;
                
                case 'application/vnd.google-apps.form':
                case 'application/vnd.google-apps.freebird':
                    $url=$file['alternateLink'];
                    $_url=explode('?',$url);
                    $url=preg_replace('~/edit$~','/viewform',$_url[0]);
                    $need_to_publish = false;
                    break;
                
                case 'application/vnd.google-apps.presentation':
                    $url=$file['embedLink'];
                    $_url=explode('?',$url);                    
                    $url=preg_replace('~/preview$~','/embed',$_url[0]);
                    $url.='?start='.($this->data['start']?'true':'false');
                    $url.='&loop='.($this->data['loop']?'true':'false');
                    $url.='&delayms='.($this->data['delayms']?:'3000');
                    break;
                
                case 'application/vnd.google-apps.folder':
                    $need_to_publish = false;
                    $need_to_share = true;
                    $url='https://drive.google.com/embeddedfolderview?id='.$this->data['id'].'#'.($this->data['view']?:'list');
                    
                    break;
                
                default:                                      
                    $url='https://docs.google.com/file/d/'.$this->data['id'].'/preview';
                    $need_to_publish = false;
                    $need_to_share = true;
                    break;
                    
            }
            
            
        
            
            
            if ($need_to_share) {
                $permissions=$service->permissions->list(array('fileId'=>$this->data['id']));
                
                
                foreach($permissions['items'] AS $item)
                {
                    if ($item['type']=='anyone')
                    {
                        $need_to_share = false;
                    }
                }
                
                if ($need_to_share)
                {
                    $permission=new Google_Permission();
                    $permission->type='anyone';
                    $permission->role='reader';
                    $permission->withLink=true;
                    $permission->value='';
                    
                    $service->permissions->insert($this->data['id'], $permission);
                    
                }
                
            
            }
            
            
            
            //mydie($file,$url);
            
            $this->data['url']=$url;
            
            if ($need_to_publish) {
            
                $revisions=$service->revisions->list(array('fileId'=>$this->data['id']));
                
                foreach($revisions['items'] AS $item)
                {
                    $revision=$item;
                    
                    if ($item['published']) {
                        if ($item['publishAuto'] && $item['publishedOutsideDomain']) $need_to_publish=false; 
                        break;
                    }
                }
                
                if ($need_to_publish) {
                    $revision['published'] = true;
                    $revision['publishAuto'] = true;
                    $revision['publishedOutsideDomain'] = true;
                    $service->revisions->update($this->data['id'],$revision['id'],new Google_Revision($revision));
                }
            }
            
            //mydie(array('file'=>$file,'revision'=>$revision),$url);
            
        }
        
        
        return parent::update();
    }
}