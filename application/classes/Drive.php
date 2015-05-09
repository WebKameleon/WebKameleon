<?php

class Drive extends Google {


    protected static function request($url,$method='GET',&$data,$headers=array())
    {
        return parent::request($url,$method,$data,'drive','json',null,$headers);
    }

    public static function getFile($fileId)
    {
        $url='https://www.googleapis.com/drive/v2/files/'.$fileId;
        
        return self::request($url,'GET',null);
    }
    
    public static function getFileChildren($fileId,$title='')
    {
        $url='https://www.googleapis.com/drive/v2/files/'.$fileId.'/children';
        if ($title) $url.='?q='.urlencode('title="'.$title.'"');
        
        
        return self::request($url,'GET',null);
    }

    public static function createFolder($title,$parent=null)
    {
        
        $metadata=array('title'=>$title,'mimeType'=>'application/vnd.google-apps.folder');
        if ($parent) $metadata['parents']=array(array('id'=>$parent));
                
        return self::request('https://www.googleapis.com/drive/v2/files','POST',$metadata);
    }

    
    
    public static function uploadFile($title,$type,$data,$folder=null,$convert=false,$data_is_file=false)
    {

        $boundary=md5(time());

        
        $url='https://www.googleapis.com/upload/drive/v2/files?uploadType=multipart&convert='.($convert?'true':'false');
        
        $header=array('Content-Type'=>'multipart/related; boundary="'.$boundary.'"');

        
        $metadata=array('title'=>$title,'mimeType'=>$type);
        if ($folder) $metadata['parents']=array(array('id'=>$folder));
        
        $body="--$boundary\nContent-Type: application/json; charset=UTF-8\n\n";
        $body.=json_encode($metadata);
        
        $body.="\n\n--$boundary\nContent-Type: $type\n\n";

        
        if (!$data_is_file) $body.=$data;
	else $body.=file_get_contents($data);
        
        $body.="\n--$boundary--";
    
        $ret=self::request($url,'POST',$body,$header);
        
        return $ret;
    }

    
    

}
