<?php
/**
 * @author Piotr Podstawski <piotr.podstawski@gammanet.pl>
 */

 
class Html
{
    
    public static function __callStatic($name,$args)
    {
        return $args[0];
    }
    
    public static function beautify($html,$page=0)
    {
        $html=preg_replace('~\n\s*\n+~', "\n", $html);

        $tags_selfended = array('link','meta','br','hr','input','!doctype');
        
        
        // first run; create table with tags and content ($htmla)
        $htmla=array();
        while (($pos=strpos($html,"<"))!==false) {

            if ($pos) {
                $htmla[]=array('tag'=>false,'content'=>substr($html,0,$pos),'len'=>$pos,'script'=>false);
                $html=substr($html,$pos);
            }
            
            if (substr($html,1,1)=='?') {
                $end=strpos($html,"?>");
                if ($end) $end+=2;
            } else {
                $end=strpos($html,">");
                if ($end) $end+=1;
            }

            if (!$end) {
                $htmla[]=array('tag'=>false,'content'=>$html, 'noend'=>true,'script'=>false, 'len'=>strlen($html));
                break;
            }

            $tag=substr($html,0,$end);
            $html=substr($html,$end);
            
            $pos=strpos($tag,' ');
            if ($pos) $t=substr($tag,1,$pos-1);
            else $t=substr($tag,1,strlen($tag)-2);
            
            $t=strtolower($t);
            $tag_selfend=in_array($t,$tags_selfended);
                     
            $htmla[]=array('tag'=>$t,'content'=>$tag,'len'=>$end,'selfend'=>substr($tag,-2,1)=='/' || $tag_selfend, 'script'=>false);
        
        
            if (in_array($t,array('script','style'))) {
                $pos=strpos(strtolower($html),"</$t>");
                if ($pos) {
                    $htmla[]=array('tag'=>false,'content'=>substr($html,0,$pos),'script'=>true,'len'=>$pos);
                    $html=substr($html,$pos);
                }
            }
        }
        
        // second run; count len and number of subtags
        $tab=0;
        $tab_inside=array();
        
        foreach ($htmla AS $i=>&$t)
        {
            $t['tab']=$tab;
            
            if (!isset($tab_inside[$tab])) $tab_inside[$tab]=array('tags'=>0,'len'=>0,'count'=>0,'parent'=>$i-1);
            

            for ($j=0;$j<=$tab;$j++) {
                $tab_inside[$j]['len']+=$t['len'];
                $tab_inside[$j]['count']++;
            }
            
            $t['parent']=$tab_inside[$tab]['parent'];
            
            if ($t['tag']) {
                $tab_inside[$tab]['tags']++;
                if (!$t['selfend'])
                {
                    if ($t['tag'][0]=='/') {
                        $t['tab']=--$tab;
                        
                        
                        $tags=$tab_inside[$tab+1]['tags'];
                        $count=$tab_inside[$tab+1]['count'];
                        $len=$tab_inside[$tab+1]['len'];
                        
                        unset($tab_inside[$tab+1]);
                        
                        $htmla[$i-$count]['tags']=$tags-1;
                        $htmla[$i-$count]['sublen']=$len;
                    }
                    else {
                        $tab++;
                    }
                }
            } elseif (!$t['script']) {
                $t['content']=trim($t['content']);
                $t['len']=strlen($t['content']);
            }
            
            
        }

        

        // third run; decide witch tags should go to next line
        $tab=0;

        foreach ($htmla AS $i=>&$t)
        {
            if ($t['parent']>0) {
                $k=$t['parent'];
                if (!$t['script']) {
                    if ($htmla[$k]['tags']<3 && $htmla[$k]['sublen']<100) {
                        $t['tab']=-1;
                    }
                }
            }
            
            if (!$t['len'] || !strlen($t['content'])) unset($htmla[$i]);
            
        }
        
        
        // last run; create html
        $html='';
        
        foreach ($htmla AS &$t)
        {
            if ($t['tab']>=0) {
                
                if (!$t['script']) {
                    $html.="\n";
                    $html.=str_repeat("\t",$t['tab']); 
                } else {
                    $tab='';
                    for($i=0;$i<strlen($t['content']);$i++) {
                        if ($t['content'][$i]=="\n") {
                            $tab='';
                        } elseif ($t['content'][$i]=="\t" || $t['content'][$i]==" ") {
                            $tab.=$t['content'][$i];
                        } else {
                            break;
                        }
                    }
                    
                    $t['content']=preg_replace('~'.$tab.'(.*)\n~',str_repeat("\t",$t['tab'])."\\1\n",$t['content']);
                    
                    
                    if (!strlen(trim($t['content']))) $t['content']='';
                    else $html.="\n";
                    
                    
                }
            }
            
            if ($t['tag']) {
                $t['content']=str_replace(' >','>',$t['content']);
                if (isset($t['selfend']) && $t['selfend'] && $t['tag'][0]!='!' && substr($t['content'],-2,1)!='/') {
                    //$t['content']=substr($t['content'],0,strlen($t['content'])-1).'/>';
                }
            }
            
            $html.=$t['content'];
        }       
        
        $html=trim($html);
        
                
        return $html;
        
    }
    
    public static function strip($html,$page=0)
    {
        $html=preg_replace('~>\s+<~', '><', $html);
        $html=self::_tag_replace($html,'script');
        $html=self::_tag_replace($html,'style');
        return trim($html);
    }
    
    
    protected static function _tag_replace($html,$tag)
    {
        $oryginal_html=$html;
        
        while (($pos=strpos(strtolower($html),"<$tag"))!==false) {
            $html=substr($html,$pos);
            $end=strpos(strtolower($html),"</$tag>");
            if (!$end) break;
            
            $contents=substr($html,0,$end+strlen($tag)+3);
            

            $contents_replaced=preg_replace('~\s+~', ' ', $contents);

            
            $html=substr($html,$end+strlen($tag)+3);
            $oryginal_html=str_replace($contents,$contents_replaced,$oryginal_html);
        }
        
        
        return $oryginal_html;
    }
    
    
    public static function find_translate_font_in_tags ($html,$font='font',$class='km_translate')
    {
     
        $regex='<title><'.$font.' class="'.$class.'" rel="([^"]+)">([^>]*)</'.$font.'></title>';
        //mydie(htmlspecialchars($regex));
        $html=preg_replace('~'.$regex.'~i','<title '.$class.'="1" rel="\\1">\\2</title>',$html);     
        
        $token='="<'.$font.' class="'.$class.'" rel="';
        while (($pos=strpos($html,$token))!==false) {
        
            for ($i=$pos;$html[$i]!=' ';$i--);
            $attr=substr($html,$i+1,$pos-$i-1);
            
        
            $relend=strpos(substr($html,$pos+strlen($token)),'"');
            $rel=substr($html,$pos+strlen($token),$relend);
            $rel.=','.$attr;
            
            $tagend=strpos(substr($html,$pos+strlen($token)),'>');
            
            $fontend=strpos(substr($html,$pos+strlen($token)+$tagend),'</'.$font.'>');
            
            
            $value=substr($html,$pos+strlen($token)+$tagend+1,$fontend-1);
            
            $result=$attr.'="'.$value.'" '.$class.'="1" rel="'.$rel.'"';
            
            $tag=substr($html,$i+1,strlen($token)+$tagend+$fontend+strlen($font)+3+6);
            
            
            $html=str_replace($tag,$result,$html);
        
        }
        

        
        return $html;
    }
    
    
    public static function deffer_jscss($html,$page=0) {
        $exclude=explode(',',Bootstrap::$main->getConfig('default.ftp.deffer_exclude'));
        if (in_array($page,$exclude)) return $html;
        
        $links=array();
        $script='';
        if(preg_match_all('~<link [^>]+>~i',$html,$links)) {
            
            $script='function add_css_file(node,defer) { if (!defer) {var head = document.getElementsByTagName("head")[0];head.parentNode.insertBefore(node, head);} else { window.addEventListener("load", function() {document.getElementsByTagName("body")[0].appendChild(node);} ); };};';
            $script.=' var defer_width=document.getElementsByTagName("body")[0].offsetWidth; var defer_css=function() { ';
            foreach($links[0] AS $i=>$link) {
                if (strlen($link)<10 || strstr($link,'shortcut icon')) {
                    unset($links[0][$i]);
                } else {
                    $html=str_replace($link,'',$html);
                    $link=str_replace("'",'"',$link);
                    $script.="";
                    $linka=array();
                    if (preg_match_all('~ ([^ =]+)="([^"]+)"~',$link,$linka))
                    {
                        $script.='var node=document.createElement("link");';
                        $defer='false';
                        for ($i=0;$i<count($linka[1]);$i++) {
                            if ($linka[1][$i]=='defer') {
                                if ($linka[2][$i]) $defer='true';
                                continue;
                            }
                            $script.='node.'.$linka[1][$i].'="'.$linka[2][$i].'";';
                        }
                        //$script.='head.appendChild(node);';
                        $script.='add_css_file(node,'.$defer.');';
                    }
                    
                }
            }
            $script.='};';
            
            //$script.='var raf = requestAnimationFrame || mozRequestAnimationFrame || webkitRequestAnimationFrame || msRequestAnimationFrame;';
            $script.='if (defer_width>470) defer_css(); else { window.addEventListener("load", function() {setTimeout(defer_css,0);});};';

            
            //mydie(htmlspecialchars($script));
        }
        
        $scripts=array();
            
        if (preg_match_all('~<script([^>]*)>(.*?)</script>~si',$html,$scripts)) {
            for ($i=0;$i<count($scripts[0]);$i++) {
                if (!trim($scripts[2][$i])) {
                    $html=str_replace($scripts[0][$i],'<script defer="defer"'.substr($scripts[0][$i],7),$html);
                }
            }
        }
        
        
        //if ($script) $html=str_replace('</head>',"<script>$script</script>\n</head>",$html);
        if ($script) $html=str_replace('<body>',"<body>\n<script>$script</script>\n",$html);
        
        
        return $html;
    }
}