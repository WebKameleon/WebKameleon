<?php

    define ('TEMPLATES',__DIR__.'/.convert_template.resources');
    $lang=null;
    

    function usage($msg=null) {
        if ($msg) echo "$msg\n";
        echo 'Usage: php '.$_SERVER['argv'][0].' source_dir destination_dir [lang]'."\n";
        echo "  source_dir must exist, destination_dir must not extst!\n";
        die();
    }


    function copyr($source, $dest)
    {
       if (is_file($source)) {
          return copy($source, $dest);
       }
    
       if (!is_dir($dest)) {
          mkdir($dest);
       }

       foreach (scandir($source) AS $entry)
       {
     
          if ($entry == '.' || $entry == '..') {
             continue;
          }
    
          if ($dest !== "$source/$entry") {
             copyr("$source/$entry", "$dest/$entry");
          }
       }
    
       return true;
    }

    function label($txt) {
        global $lang;
        
        if (is_array($lang) && isset($lang[$txt])) return $lang[$txt];
        return $txt;
    }

    function get_const($_const_dir) {
        $ver='{ver}';
        $lang='{lang}';
        
        if (file_exists("$_const_dir/const.php"))
            include("$_const_dir/const.php");
        elseif (file_exists("$_const_dir/const.h"))
            include("$_const_dir/const.h");
    
    
        $ret=get_defined_vars();
        unset($ret['_const_dir']);
        unset($ret['lang']);
        unset($ret['ver']);
        return $ret;
    }
    
    function createConfig($const) {
        $config=array();
        $templates=array();
        foreach ($const AS $k =>$v) {
            $conf=null;
            switch ($k) {
                case 'CONST_PRE_H':
                    $conf="webpage.pre = '$v'";
                    break;
                case 'CONST_POST_H':
                    $conf="webpage.post = '$v'";
                    break;
                case 'CONST_ACTION_H':
                    $conf="webpage.action = '$v'";
                    break;
                case 'CONST_LANGS':
                    $conf=array();
                    foreach ($v AS $l) $conf[]='langs[] = '."'$l'";
                    break;
                    
                case 'CONST_NEXT_PAGE_LINK_FOLLOW':
                    $conf='webpage.follow_next = '.($v ? 'true':'false');
                    break;
                
                case 'C_DIRECTORY_INDEX':
                    $conf=array();
                    foreach ($v AS $i) $conf[]='default.directory_index[] = '."'$i'";
                    break;
                
                case 'C_MULTI_HF':
                    $conf='header_footer.multi = '.($v ? 'true':'false');
                    break;
                
                case 'TD_POZIOMY':
                    $conf=array();
                    foreach ($v AS $td) {
                        $conf[]='level.body.'.$td[0].' = '."'".$td[1]."'";
                    }
                    break;
                
                case 'TD_POZIOMY_HF':
                    $conf=array();
                    foreach ($v AS $td) {
                        $conf[]='level.header.'.$td[0].' = '."'".$td[1]."'";
                        $conf[]='level.footer.'.$td[0].' = '."'".$td[1]."'";
                    }
                    break;
                
                case 'DEFAULT_TD_LEVEL':
                    $conf=array("default.level.header = ".$v,"default.level.body = ".$v,"default.level.footer = ".$v);
                    break;
                
                case 'PAGE_TYPY':
                    $conf=array();
                    foreach ($v AS $page) {
                        $conf[]='webpage.type.'.$page[0].'.name = '."'".$page[1]."'";
                        $conf[]='webpage.type.'.$page[0].'.filename = '."'".$page[2]."'";
                        $templates[$page[2]]=1;
                    }
                    break;
                
                case 'LINK_TYPY':
                    $conf=array();
                    foreach ($v AS $link) {
                        $conf[]='weblink.type.'.$link[0].'.name = '."'".$link[1]."'";
                        $conf[]='weblink.type.'.$link[0].'.filename = '."'".$link[2]."'";
                        $templates[$link[2]]=1;
                    }
                    break;                
                
                case 'TD_TYPY':
                    $conf=array();
                    foreach ($v AS $td) {
                        if (!strlen($td[2])) continue;
                        $conf[]='webtd.type.'.$td[0].'.name = '."'".$td[1]."'";
                        $conf[]='webtd.type.'.$td[0].'.filename = '."'".$td[2]."'";
                        $templates[$td[2]]=1;
                    }
                    break;
                
                
                case 'C_BLOCK_HEADER_EDIT':
                    if ($v) $conf=array("default.header.forbidden = true","default.footer.forbidden = true");
                    break;
                
                
                case 'TD_TYPY_DXML':
                    $conf=array();
                    foreach ($v AS $type=>$keys) {
                        foreach ($keys AS $key=>$a) {
                            $conf[]='webtd.user.'.$type.'.'.$key.'.label = "'.$a[0].'"';       
                            if ($a[1]) $conf[]='webtd.user.'.$type.'.'.$key.'.style = "'.$a[1].'"';
                            if ($a[2]) $conf[]='webtd.user.'.$type.'.'.$key.'.values = "'.$a[2].'"';
                        }
                    }                    
                    break;
                case 'PAGE_TYPY_DXML':
                    $conf=array();
                    foreach ($v AS $type=>$keys) {
                        foreach ($keys AS $key=>$a) {
                            $conf[]='webpage.user.'.$type.'.'.$key.'.label = "'.$a[0].'"';       
                            if ($a[1]) $conf[]='webpage.user.'.$type.'.'.$key.'.style = "'.$a[1].'"';
                            if ($a[2]) $conf[]='webpage.user.'.$type.'.'.$key.'.values = "'.$a[2].'"';
                        }
                    }                    
                    break;

                case 'LINK_TYPY_DXML':
                    $conf=array();
                    foreach ($v AS $type=>$keys) {
                        foreach ($keys AS $key=>$a) {
                            $conf[]='weblink.user.'.$type.'.'.$key.'.label = "'.$a[0].'"';       
                            if ($a[1]) $conf[]='weblink.user.'.$type.'.'.$key.'.style = "'.$a[1].'"';
                            if ($a[2]) $conf[]='weblink.user.'.$type.'.'.$key.'.values = "'.$a[2].'"';
                        }
                    }                    
                    break;                
                    
                case 'CONST_FTP_PASSIVE':
                case 'CONST_TOKENS':
                case 'CONST_PARSER_INTEGRATED':
                case 'CONST_PARSER_TOKENS':
                case 'C_DEBUG_MODE':
                case 'C_EDITOR_FORM':
                case 'C_SWF_STYLE':
                case 'C_CONTENT_EDITABLE':
                case 'CONST_REMOTE_INCLUDES_ARE_HERE':
                case 'C_FORGET_DOCBASE':
                case 'C_PAGE_WIDTH':
                case 'C_PAGE_ALIGN':
                case 'C_PAGE_MENULEFTWIDTH':
                case 'C_PAGE_MENURIGHTWIDTH':
                case 'C_SHOW_OLD_SUPPORT':
                case 'C_SITECREDITS':
                    break;
                    

                default:
                    if (substr($k,0,13)=='DEFAULT_PATH_') {
                        $rest=str_replace(';','',substr($k,13));
                        if (strstr($rest,'$')) {
                            $rest=str_replace("\\",'',$rest);
                            $rest=str_replace('$ver','{ver}',$rest);
                            $rest=str_replace('$lang','{lang}',$rest);
                            
                        }
                        $rest=str_replace('PAGES_PREFIX','pageprefix',$rest);
                        $conf='path.'.trim(strtolower($rest)).' = '."'$v'";
                    } elseif (substr($k,0,9)=='C_WIDGET_') {
                        $rest=strtolower(substr($k,9));
                        $rest=preg_replace('/_w$/','_width',$rest);
                        $rest=preg_replace('/_h$/','_height',$rest);
                        
                        $conf='widgets.'.str_replace('_','.',$rest).' = '."'$v'";
                        
                    } elseif (substr($k,0,7)=='C_SHOW_') {
                        $rest=explode('_',strtolower(substr($k,7)));
                        
                        $what=$rest[0];
                        unset($rest[0]);
                        
                        $conf = "web${what}.show.".implode('_',$rest). ' = '.($v?'true':'false');
                    } elseif (substr($k,0,7)=='C_HIDE_') {
                        //forget it
                    }
                    else {
                        $conf=';'.$k.'='.json_encode($v);
                        
                        echo "case '$k':\n";
                    }
                    
                    
                    
                    break;

            }
            if ($conf)
            {
                if (is_array($conf)) {
                    foreach ($conf AS $c) {
                        $config[$c]=$c;
                    }
                }
                else $config[$conf]=$conf;
            }
        }
        
        $config2=array();
        foreach ($config AS $c) $config2[]=$c;
        
        return array('config'=>$config2,'templates'=>array_keys($templates));
    }

    
    
    function parseHtml($f,$dir) {
        $html=file_get_contents($f);
        
        $tokens=array();
        
        while (($pos=strpos($html,'<!--'))!==false) {
            $end=strpos($html,'-->');
            if (!$end) break;
            $html=trim(substr($html,0,$pos).substr($html,$end+3));
        }
        
        $delim=md5(time());
        $html2=preg_replace('/%([^%\n]+)%/',$delim." \\1 ".$delim,$html);
        
        while (($pos=strpos($html2,$delim.' '))!==false) {
            $html2=substr($html2,$pos+33);
            $end=strpos($html2,' '.$delim);
            $token=substr($html2,0,$end);
            $html2=substr($html2,strlen($token)+33);
            
            $token2=strtolower($token);
            
            if (isset($tokens[$token2])) continue;
            
            $replace='{tokens.'.$token2.'}';
            $tokens[$token2]='';
            
            $missed_count=0;
            foreach (array(TEMPLATES,TEMPLATES.'/'.basename($dir)) AS $template_dir)
            {
                if (file_exists($template_dir.'/'.$token2.'.token')) 
                    $tokens[$token2]=file_get_contents($template_dir.'/'.$token2.'.token'); 
                
                if (file_exists($template_dir.'/'.$token2.'.html')) {
                    $replace=trim(file_get_contents($template_dir.'/'.$token2.'.html'));
                } elseif (file_exists($template_dir.'/'.$token2.'.php')) {
                    ob_start();
                    include($template_dir.'/'.$token2.'.php');
                    $replace=trim(ob_get_clean());
                    ob_end_clean();
                } elseif (preg_match('/web(body|header|footer)_level[0-9]+/',$token2)) {
                    $replace='{'.preg_replace('/web(body|header|footer)_/','',$token2).'}';
                } elseif (file_exists($template_dir.'/'.$token2.'.token')) {
        
                } else {
                    $missed_count++;
                }
                
            }
            if ($missed_count==2) echo "Unknown token $token (".basename($f).")\n";
            
            $html=str_replace("%${token}%",$replace,$html);
        }
                
        
        $html=trim($html);
        $html=preg_replace("/\n([ \n\t]*)\n/","\n",$html);
        
        return array('html'=>$html,'tokens'=>$tokens);
    }

    /**************************** main ****************************/

    if (count($_SERVER['argv'])!=3 && count($_SERVER['argv'])!=4) usage();

    $source=$_SERVER['argv'][1];
    $dest=$_SERVER['argv'][2];
    
    if (!file_exists($source) || !is_dir($source)) usage("Source does not exist or is not a dir!");
    if (file_exists($dest)) usage("Destination path exists!");
    
    if (isset($_SERVER['argv'][3])) {
        $l=$_SERVER['argv'][3];
        if (file_exists(__DIR__.'../application/lang/'.$l.'.php')) {
            include(__DIR__.'../application/lang/'.$l.'.php');
            $lang=$words;
        }
    }
    
    
    $const=get_const($source);
    
    $config=createConfig($const);
    
    

    mkdir ($dest,0755,true);
    copyr("$source/images","$dest/images");
    
    file_put_contents("$dest/config.ini",implode("\n",$config['config']));
    mkdir ("$dest/html");


    $tokens=array();
    foreach ($config['templates'] AS $template) {

        $html=parseHtml("$source/themes/$template",$source);
        
        if (strstr($template,'/')) mkdir("$dest/html/".dirname($template),0755,true);
        file_put_contents("$dest/html/$template",$html['html']);
    
	$tokens=array_merge($tokens,$html['tokens']);
    }
    
    
    $templateTokens="<?php\nclass TemplateTokens extends Tokens {\n";
    
    foreach($tokens AS $token=>$v)
    {
        if (!$v) continue;
        
        $templateTokens.="\n$v";
    }
    $templateTokens.="\n}";
    
    file_put_contents("$dest/TemplateTokens.php",$templateTokens);

    

    if (file_exists("$source/.miniatura.jpg")) copy ("$source/.miniatura.jpg","$dest/.thumbnail.jpg");