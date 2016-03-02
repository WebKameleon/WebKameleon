<?php


class Tools
{
    /**
     * @param string $directory
     * @return array
     */
    public static function list_templates($directory = null)
    {
        $PATH = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates';

        if ($directory == null) {
            $directory = $PATH;
        }
        

        $templates = array();
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . 'config.ini')) {
                
                if (file_exists($dir . DIRECTORY_SEPARATOR . '.langs.txt'))
                {
                    $langs=explode(',',trim(file_get_contents($dir . DIRECTORY_SEPARATOR . '.langs.txt')));
                    if (!in_array(Bootstrap::$main->session('ulang'),$langs)) continue;
                }
                
                $templates[] = str_replace($PATH . DIRECTORY_SEPARATOR, '', $dir);
            } else {
                $templates = array_merge($templates, self::list_templates($dir));
            }
        }

        
        return $templates;
    }

    /**
     * @param string $template
     * @return string
     */
    public static function get_template_thumb($template,$thumbnail='.thumbnail',$return_file_name_rather_then_contents=false)
    {
        if ($template instanceof serverModel) {
            $template = $template->data();
        }

        $paths = array();

        if (is_array($template)) {
            $paths[] = MEDIA_PATH . '/' . $template['nazwa'] . '/template/' . $template['ver'] . '/'.$thumbnail.'.jpg';
            $paths[] = MEDIA_PATH . '/' . $template['nazwa'] . '/template/' . $template['ver'] . '/'.$thumbnail.'.png';

            $paths[] = MEDIA_PATH . '/templates/' . trim($template['szablon']) . '/' . $template['ver'] . '/'.$thumbnail.'.jpg';
            $paths[] = MEDIA_PATH . '/templates/' . trim($template['szablon']) . '/' . $template['ver'] . '/'.$thumbnail.'.png';
            
            $paths[] = APPLICATION_PATH . '/templates/' . trim($template['szablon']) . '/'.$thumbnail.'.jpg';
            $paths[] = APPLICATION_PATH . '/templates/' . trim($template['szablon']) . '/'.$thumbnail.'.png';
            
            $paths[] = APPLICATION_PATH . '/templates/.' . trim($template['szablon']) . '/'.$thumbnail.'.jpg';
            $paths[] = APPLICATION_PATH . '/templates/.' . trim($template['szablon']) . '/'.$thumbnail.'.png';            
            
        }

        if (is_string($template)) {
            $paths[] = APPLICATION_PATH . '/templates/' . trim($template) . '/'.$thumbnail.'.jpg';
            $paths[] = APPLICATION_PATH . '/templates/' . trim($template) . '/'.$thumbnail.'.png';
            
            $paths[] = APPLICATION_PATH . '/templates/.' . trim($template) . '/'.$thumbnail.'.jpg';
            $paths[] = APPLICATION_PATH . '/templates/.' . trim($template) . '/'.$thumbnail.'.png';
        }


        $paths[] = APPLICATION_PATH . '/templates/.default/'.$thumbnail.'.jpg';
        $paths[] = APPLICATION_PATH . '/templates/.default/'.$thumbnail.'.png';


        foreach ($paths as $path) {
            if (file_exists($path))
            {
                if ($return_file_name_rather_then_contents) return $path; 
                return base64_encode(file_get_contents($path));
            }
        }

    }

    /**
     * @return array
     */
    public static function get_templates()
    {
        $templates = array();
        $families = array();
        
        
        $config = Bootstrap::$main->getConfig('payment');
        $lang = Bootstrap::$main->session('ulang');
        $info = $config['default'];
        if (isset($config[$lang])) {
            $info = array_merge($info, $config[$lang]);
        }        
        
        
        foreach (Tools::list_templates() as $template) {
            
            list ($family) = explode(DIRECTORY_SEPARATOR, $template);
            $templates[$template]=array('family'=>$family,'author'=>'Webkameleon','price'=>0,'tags'=>array('webkameleon'),'desc'=>'');
            $families[$family] = Tools::translate('Family:' . $family);
        }

        $server=new serverModel();
        $social_templates = $server->templates(Bootstrap::$main->session('ulang'));
        
        foreach($social_templates AS $template) {

            $price=isset($template['social_template_price_'.$lang]) ? $template['social_template_price_'.$lang] : $template['social_template_price_en'];
            $social_template=$template['social_template']?:'my_websites';
            
            $templates['media/'.$template['nazwa']] = array(
                'family'=>$social_template,
                'author'=>$template['fullname'],
                'price'=>$price,
                'desc'=>$template['social_template_desc'],
                'tags'=>$template['social_template_tags'] ? explode(',',$template['social_template_tags']) : array(),
                'currency'=>$info['currency']
            );
    
            
            
            $families[$social_template] = Tools::translate('Family:' . $social_template);
        }
        
        
        $local = array();
        foreach (glob(APPLICATION_PATH . '/../files/*.wkz') as $wkz) {
            $name = basename($wkz);
            $local[$name] = $name . ' <i>(' . date('Y-m-d H:i', filemtime($wkz)) . ')</i>';
        }

        
        $service = Google::getDriveService();
        

        try {
            $listFiles = $service->files->listFiles(array('q' => "mimeType = 'application/x-zip' and trashed = false"));
            
            $drive = array();
            foreach ($listFiles['items'] as $file) {
                if (preg_match('/\.wkz$/', $file['title'])) {
                    //$drive[$file['id']] = $file['title'] . ' <i>(' . date('Y-m-d H:i', strtotime($file['modifiedDate'])) . ')</i>';
                    $drive[$file['id']] = array(
                        'id'=>$file['id'],
                        'date'=>$file['modifiedDate'],
                        'author'=>$file['ownerNames'][0],
                        'title' => $file['title']
                    );
                }
            }
            
            
        } catch (Exception $e) {
            $drive=false;
        }

        


        asort($families);
        
        return array('families' => array_unique($families), 'templates' => $templates, 'local' => $local, 'drive' => $drive, 'default'=>'default');
    }

    /**
     * @return string
     */
    public static function get_tmp_filename()
    {
        return tempnam(sys_get_temp_dir(), md5(time()));
    }

    /**
     * @param $path
     * @param int $mode
     * @return bool
     */
    public static function check_if_exists($path, $mode = 0777)
    {
        $exists = file_exists($path);
        if (!$exists && is_int($mode)) {
            $exists = mkdir($path, $mode, true);
        }

        return $exists;
    }

    /**
     * @param string $filename
     * @param string $srcDir
     * @param string $dstDir
     * @param int $width
     * @param int $height
     * @param int $mode
     * @param bool $scale
     * @param bool $crop
     * @return string
     */
    public static function check_image($filename, $srcDir, $dstDir, &$width, &$height, $mode = 0777, $scale = false, $crop = false)
    {
        if (!$filename) return false;
        $src = $srcDir . DIRECTORY_SEPARATOR . $filename;
        $dst = $dstDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($src)) return false;
        
        self::check_if_exists(dirname($dst), $mode);

        $check = false;

        
        
        
        if (!file_exists($dst)) {
            $check = true;
        } else {
            if (filemtime($src) > filemtime($src)) {
                $check = true;
            } elseif (($imagesize = getimagesize($dst)) !== false) {
                
                if (($imagesize[0] != $width) || ($imagesize[1] != $height) ) {
                    $check = true;
                }
            }
        }
        
        
        

        if ($check) {
            $image=new Image($src);
            $img = $image->min($dst, $width, $height, $scale, $crop);
            if ($img) list ($width, $height) = getimagesize($img);
        }
        

        return $dst;
    }

    /**
     * @return array
     */
    public static function get_mime_types()
    {
        static $mimeTypes;

        if ($mimeTypes == null) {
            $mimeTypes = array();

            $mimecf = file(APPLICATION_PATH . '/../library/elFinder/mime.types');

            foreach ($mimecf as $line_num => $line) {
                if (!preg_match('/^\s*#/', $line)) {
                    $mime = preg_split('/\s+/', $line, -1, PREG_SPLIT_NO_EMPTY);
                    for ($i = 1, $size = count($mime); $i < $size; $i++) {
                        if (!isset($mimeTypes[$mime[$i]])) {
                            $mimeTypes[$mime[$i]] = $mime[0];
                        }
                    }
                }
            }

            ksort($mimeTypes);
        }

        return $mimeTypes;
    }

    /**
     * @param string $ext
     * @return string
     */
    public static function get_mime_from_ext($ext)
    {
        $mimeTypes = self::get_mime_types();

        return @$mimeTypes[$ext];
    }

    /**
     * @param string $mime
     * @return string
     */
    public static function get_ext_from_mime($mime)
    {
        $mimeTypes = self::get_mime_types();

        return array_search($mime, $mimeTypes);
    }

    /**
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function date($timestamp, $format = null)
    {
        return Bootstrap::$main->kameleon->date($timestamp, $format);
    }

    /**
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function datetime($timestamp, $format = null)
    {
        return Bootstrap::$main->kameleon->datetime($timestamp, $format);
    }

    /**
     * @param string $date
     * @return int
     */
    public static function timestamp($date)
    {
        if (!$date) return null;
        
        if (strlen($date) < 11) $date .= ' 23:59';

        return strtotime($date);
    }

    /**
     * @param string $text
     * @return string
     */
    public static function translate($text)
    {
        return call_user_func_array(array(Bootstrap::$main->translate, 'trans'), func_get_args());
    }

    /**
     * @param $url
     * @return string
     */
    public static function url($url)
    {
        return Bootstrap::$main->getRoot() . $url;
    }

    public static function getPaths($server, $ver = 0)
    {
        $ret = array();

        $server_ver = $server['ver'];
        if (!$ver) $ver = $server_ver;
        $server_name = trim($server['nazwa']);
        $template_name = trim($server['szablon']);
        
        

        $ret['template_media_path'] = 0;

        for ($v = $ver; $v; $v--) {
            $path = MEDIA_PATH . '/' . $server_name . '/template/' . $v;

            if (file_exists($path)) {
                $ret['template_path'] = $path;
                $ret['template_media_path'] = true;
                break;
            }
        }

        if (!isset($ret['template_path'])) {
            for ($v = $ver; $v; $v--) {
                $path = MEDIA_PATH . '/templates/' . $template_name . '/' . $v;

                if (file_exists($path)) {
                    $ret['template_path'] = $path;
                    $ret['template_media_path'] = true;
                    break;
                }
            }
        }
        
        if (!isset($ret['template_path'])) {
            $path = APPLICATION_PATH . '/templates/' . $template_name;

            if (file_exists($path) && !empty($template_name)) {
                $ret['template_path'] = $path;
            }
        }

        if (!isset($ret['template_path'])) {
            $path = APPLICATION_PATH . '/templates/.' . $template_name;

            if (file_exists($path) && !empty($template_name)) {
                $ret['template_path'] = $path;
            }
        }
        
        if (!isset($ret['template_path'])) {
            $path = APPLICATION_PATH . '/templates/.default';
            if (file_exists($path)) {
                $ret['template_path'] = $path;
            }
        }

        if (!isset($ret['uincludes'])) {
            for ($v = $ver; $v; $v--) {
                $path = MEDIA_PATH . '/' . $server_name . '/include/' . $v;

                if (file_exists($path)) {
                    $ret['uincludes'] = $path;
                    $ret['uincludes_ajax'] = Bootstrap::$main->getRoot() . 'uincludes/' . $server_name . '/' . $v;
                    break;
                }
            }
        }

        if (!isset($ret['uincludes'])) {
            for ($v = $ver; $v; $v--) {
                $path = MEDIA_PATH . '/uincludes/' . $server_name . '/' . $v;

                if (file_exists($path)) {
                    $ret['uincludes'] = $path;
                    $ret['uincludes_ajax'] = Bootstrap::$main->getRoot() . 'uincludes/' . $server_name . '/' . $v;
                    break;
                }
            }

        }
        
        if (!isset($ret['uincludes'])) {
            $ret['uincludes'] = false;
            $ret['uincludes_ajax'] = false;
        }

        if (!isset($ret['uimages'])) {

            $path = MEDIA_PATH . '/' . $server_name . '/images/' . $server_ver;
            if (!file_exists($path)) {
                for ($v = $ver; $v; $v--) {
                    $path2 = MEDIA_PATH . '/uimages/' . $server['id'] . '/' . $v;

                    if (file_exists($path2)) {
                        $ret['uimages'] = Bootstrap::$main->getRoot() . 'uimages/' . $server['id'] . '/' . $v;
                        $ret['uimages_path'] = $path2;
                        break;
                    }
                }

                if (!isset($ret['uimages'])) @mkdir($path, 0755, true);
            }

            if (!isset($ret['uimages'])) {
                for ($v = $ver; $v; $v--) {
                    $path = MEDIA_PATH . '/' . $server_name . '/images/' . $v;

                    if (file_exists($path)) {
                        $ret['uimages'] = Bootstrap::$main->getRoot() . 'uimages/' . $server['id'] . '/' . $v;
                        $ret['uimages_path'] = $path;
                        break;
                    }

                }
            }

        }

        $ufiles = $server['id'] . '-att';
        $path = MEDIA_PATH . '/ufiles/' . $ufiles;

        if (!file_exists($path)) $path = MEDIA_PATH . '/' . $server_name . '/files';
        if (!file_exists($path)) @mkdir($path, 0755, true);

        $ret['ufiles'] = Bootstrap::$main->getRoot() . 'ufiles/' . $ufiles;
        $ret['ufiles_path'] = $path;

        return $ret;
    }

    
    public static function scandir($dir,$include_dirs=false)
    {
        $dirs=array();
        
        foreach(scandir($dir) AS $file) {
            if ($file[0]=='.') {
                   continue;
            }
            
            if (is_dir("$dir/$file"))
            {
                
                if ($include_dirs) $dirs[]=array('file'=>$file,'type'=>'d');
                $sub=self::scandir("$dir/$file",$include_dirs);
                
                foreach($sub AS $f)
                {
                    $dirs[]=array('file'=>$file.'/'.$f['file'],'type'=>$f['type']);
                }
                
            }
            else
            {
                $dirs[]=array('file'=>$file,'type'=>'f');
            }
        }
        
        return $dirs;
    }

    public static function rm_r($path)
    {

        if (is_dir($path)) {
            $dir = scandir($path);
            foreach ($dir AS $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                self::rm_r($path . '/' . $file);
            }
            rmdir($path);
        } elseif (file_exists($path)) {
            unlink($path);
        }
    }


    /**
     * @param string $src
     * @param string $dst
     * @param int $mode
     */
    public static function cp_r($src, $dst, $mode = 0755)
    {
        $dir = opendir($src);
        @mkdir($dst, $mode, true);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    self::cp_r($src . '/' . $file, $dst . '/' . $file, $mode);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    
    
    public static function cache($token,$value=null)
    {
        $dir=APPLICATION_PATH.'/../cache';
        if (!file_exists($dir))
        {
            @mkdir($dir,0755);
        }
        
        if (!file_exists($dir))
        {
            if (!is_null($value)) return $value;
            return null;
        }
        
        if (!is_writable($dir))
        {
            if (!is_null($value)) return $value;
            return null;
        }
        
        $file=$dir.'/'.md5(serialize($token)).'.ser';
        
        if (!is_null($value))
        {
            file_put_contents($file,serialize($value));
            return $value;
        }
        
        if (!file_exists($file)) return null;
        
        $config=Bootstrap::$main->getConfig('cache');
        
        if (filemtime($file)<time()-$config['timeout']) return null;
        
        return unserialize(file_get_contents($file));
        
    }
    
    public static function log($file,$array)
    {
        $date=date('Y-m-d H:i');
        $server=Bootstrap::$main->session('server');
        $nazwa=isset($server['nazwa'])?$server['nazwa']:"NONE";
        $log='['.$date.' '.$nazwa.']: ';
        
        foreach($array AS $txt)
        {
            @file_put_contents(__DIR__.'/../logs/'.$file.'.log',$log.print_r($txt,1),FILE_APPEND);
        }
        
    }
    
    protected static $activity_now;
    
    public static function activity($table,$table_id,$type='R')
    {
        
        if (self::$activity_now==Bootstrap::$main->now) return;
        
        self::$activity_now=Bootstrap::$main->now;
        
        $server=Bootstrap::$main->session('server');
        if (isset($server['login_id']) && $server['login_id'])
        {
            $login_id=$server['login_id'];
        }
        else
        {
            $login_id=0+Bootstrap::$main->session('no_server_login_id');
        }
        
        
        
        if (!$login_id) return;
        
        
        
        
        $activity=new activityModel();
        
        $activity->login_id = $login_id;
        $activity->nd_click = Bootstrap::$main->now;
        $activity->click_type = substr($type,0,1);
        $activity->table_name = $table;
        $activity->table_id = $table_id;
        
        $activity->save();
    }
    
    
    public static function ranges($range)
    {
        $range=str_replace(',',';',$range);
        $ranges=explode(";",$range);
        $result=array();
        
        for ($i=0;$i<count($ranges);$i++)
        {
            $oddo=explode("-",$ranges[$i]);        
        
            if (isset($oddo[1]) && $oddo[0]+0<$oddo[1]+0)
            {
                $oddo[0]+=0;
                $oddo[1]+=0;
                $result[]=$oddo;
            }
        }
        
        return $result;
        
    }
    
    public static function nohtml($str,$tags_allowed=array()) {
        foreach ($tags_allowed AS $t)
        {
            $tag=md5('<'.$t);
            $notag=md5('>'.$t);
            $endtag=md5('</'.$t);
            $str=preg_replace('~<'.$t.'([^>]*)>~i',"$tag~\\1~$notag",$str);
            $str=preg_replace('~</'.$t.'>~i',$endtag,$str);
        }
        
        
        $str=preg_replace('/<[^>]*>/','',$str);
        $str=preg_replace('/<[^>]*$/','',$str);
        //$str=preg_replace('/&[^;]+;/',' ',$str);


        foreach ($tags_allowed AS $t)
        {
            $tag=md5('<'.$t);
            $notag=md5('>'.$t);
            $endtag=md5('</'.$t);
            $str=str_replace($tag.'~',"<$t ",$str);
            $str=str_replace('~'.$notag,'>',$str);
            $str=str_replace($endtag,'</'.$t.'>',$str);
            
        }
        
        return $str;
        
    }

}