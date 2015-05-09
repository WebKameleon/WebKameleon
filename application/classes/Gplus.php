<?php

class Gplus extends Google {
    
    public static $user=null;

    protected static function request($url,$method='GET',$data=null,$headers=array(),$scopes='plus')
    {
        return parent::request($url,$method,$data,$scopes,'json',self::$user,$headers);
    }


    
    public static function people_list($id='me',$collection='visible')
    {
        $url='https://www.googleapis.com/plus/v1/people/'.$id.'/people/'.$collection;
        $people=self::request($url);
        $people2=$people;
    
    
        while (isset($people2['totalItems']) && isset($people2['nextPageToken']) && $people2['totalItems']>count($people['items']))
        {
            $people2=self::request($url.'?pageToken='.$people2['nextPageToken']);
            if (isset($people2['items']) && count($people2['items'])) $people['items'] = array_merge($people['items'],$people2['items']);
            else break;
        }
    
        return $people;
    }
    
    
    public static function upload_img($img,$id='me')
    {
       
        $url='https://www.googleapis.com/upload/plusDomains/v1/people/'.$id.'/media/cloud';
        
        $ext=end(explode('.',$img));
        
        return self::request($url,'POST',file_get_contents($img),array('Content-Type'=>'image/'.$ext),'newsletter');
    }

}