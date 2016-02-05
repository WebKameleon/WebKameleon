<?php
class Kameleon
{

    protected $root;

    public function __construct($root)
    {
        $this->root = $root;
    }

    public function unhtml($html)
    {
        $html = preg_replace("/[\n\r\t]+/", " ", $html);
        $html = str_replace('&nbsp;', ' ', $html);

        $html = preg_replace("#<br[^>]*>#i", "\n", $html);
        $html = preg_replace("#</p>#i", "\n\n", $html);
        $html = preg_replace("#</div>#i", "\n\n", $html);
        $html = preg_replace("#</h[0-9]>#i", "\n\n", $html);
        $html = preg_replace("#</li>#i", "\n\n", $html);
        $html = preg_replace("#</option>#i", "\n", $html);
        $html = preg_replace("#</textarea>#i", "\n", $html);

        $html = preg_replace("#<[^>]+>#", "", $html);

        $html = preg_replace("/[ ]+/", " ", $html);
        $html = preg_replace("/\n[\n]+/", "\n\n", $html);

        return trim($html);

    }

    public function href($href, $variables, $page_target, $referer, $mode, $follow_link_if_const = true)
    {

        $lang_target = '';
        if (strstr($page_target, ':')) {
            $_page_target = explode(':', $page_target);
            $lang_target = $_page_target[0];
            $page_target = trim($_page_target[1]);
        }
        
        
        $href=trim($href);
        $hash='';
        if (!empty($href) && $href[0]='#')
        {
            $hash=$href;
            $href='';
        }

        
        
        if (empty($href)) {

            
        
            $webpage = new webpageModel();

            if ($follow_link_if_const && $mode <= PAGE_MODE_PREVIEW && strlen($page_target)) $page_target = $webpage->next_page($page_target+0);

            
            
            if ($mode) {
                if ($mode > PAGE_MODE_PREVIEW && !strlen($page_target)) {
                    $page_target = -1;
                }

                if (strlen($page_target)) $href = $this->root . 'index/get/' . $page_target;
                if ($lang_target) $href .= "?setLang=" . $lang_target;
            } else { 
                if(strlen($page_target))
                {
                    $lang = Bootstrap::$main->session('lang');
                    $should_reset_globals = false;
                    if ($lang_target && $lang_target != $lang) {
                        
                        $webpage->getMain()->file_name();
                        Bootstrap::$main->setPath($lang_target);
                        $should_reset_globals = true;
    
                    }
                    $webpage = new webpageModel();
                    if ($lang_target) $webpage->lang=$lang_target; 
                    $href = $this->relative_dir($webpage->getMain()->file_name(), $webpage->file_name($page_target + 0));
                    
                    if ($should_reset_globals) {
                        Bootstrap::$main->setPath($lang);   
                    }
                }
            }
            if ($mode > PAGE_MODE_PREVIEW) {
                if ($variables) $variables .= '&';
                $variables .= "referer=$referer";
            }
            
        }

       
        
        if ($variables) {
            $href .= strstr($href, '?') ? '&' : '?';
            $href .= $variables;
        } elseif (!$mode) {

            $default = Bootstrap::$main->getConfig('default');

            foreach ($default['directory_index'] AS $index) {
                if (substr($href, -1 * strlen($index)) == $index) {
                    $href = substr($href, 0, strlen($href) - strlen($index));
                    if (!strlen($href)) $href = '.';
                    continue;
                }
            }
        }
        
        return $href.$hash;
    }


    

    public function min_image($img, $dst, $dst_w, $dst_h, $scale = false, $crop = false)
    {
        $image=new Image($img);
        return $image->min($dst,$dst_w,$dst_h,$scale,$crop);
    }

    public function relative_dir($myself, $target)
    {
        $myself = preg_replace("#^\./#", "", $myself);
        $target = preg_replace("#^\./#", "", $target);
        $myself = preg_replace("#/\./#", "/", $myself);
        $target = preg_replace("#/\./#", "/", $target);

        $me = explode("/", $myself);
        $him = explode("/", $target);
        $wynik = '';
        $up = '';

        $the_same = 1;
        for ($i = 0; $i < count($me) - 1; $i++) {
            if (!isset($him[$i])) $him[$i] = '';

            if ($me[$i] != $him[$i]) $the_same = 0;

            if (!$the_same) {
                $up .= "../";
                if (strlen($wynik) && strlen($him[$i])) $wynik .= "/";
                $wynik .= "$him[$i]";
            }
        }
        for (; $i < count($him); $i++) {
            if (strlen($wynik)) $wynik .= "/";
            $wynik .= "$him[$i]";
        }
        $wynik = "$up$wynik";

        return $wynik;
    }

    public function datetime($time, $format = null, $format_token = 'datetime_format')
    {
        return self::date($time, $format, $format_token);
    }

    public function date($time, $format = null, $format_token = 'date_format')
    {
        if (!$time) return;

        if (!$format) {
            $config = Bootstrap::$main->getConfig();
            $format = isset($config['default'][$format_token]) ? $config['default'][$format_token] : $config['default.'.$format_token];
        }

        return @date($format, $time + Bootstrap::$main->session('time_delta'));
    }

    public function fullname($user)
    {
        $model = new userModel($user);
        $name = $model->fullname;

        return $name ? : $user;
    }

    public function include_plain($plain, $mode, $level = 0)
    {
        static $identifiers;
        $webtd = new webtdModel();

        if (!$level) $identifiers = array();

        $plain = preg_replace('#<img[^>]* name="([^">]+)"[^>]*src="[\.\/]+img/include.gif"[^>]*>#i', '{[\1]}', $plain);
        $plain = preg_replace('#<maska[^>]* name="([^">]+)"[^>]*></maska>#i', '{[\1]}', $plain);
        $plain = preg_replace('#<mask[^>]* name="([^">]+)"[^>]*></mask>#i', '{[\1]}', $plain);

        $p = $plain;
        $mids = array();

        while (1) {
            $pos1 = strpos($p, '{[');
            if (!strlen($pos1)) break;
            $pos2 = strpos($p, ']}');
            if (!strlen($pos2)) break;
            if ($pos1 > $pos2) break;

            $mid = substr($p, $pos1 + 2, $pos2 - $pos1 - 2);
            $mids[] = $mid;
            $p = substr($p, $pos2 + 2);
        }

        foreach ($mids AS $mid) {
            if (in_array($mid, $identifiers)) {
                $plain = str_replace("{[$mid]}", "", $plain);
                continue;
            }

            $td = $webtd->find_one_by_uniqueid($mid);

            $start_span = '';
            $stop_span = '';
            if ($mode > PAGE_MODE_PREVIEW) {
                $title = str_replace('"', '&quot;', $td['title']);
                $start_span = '<div class="km_mask" rel="'.$mid.'" title="' . Tools::translate('Text referenced from page') . ' ' . $td['page_id'] . ' (' . $title . ')">';
                $stop_span = '</div>';
            }
            $plain = str_replace("{[$mid]}", $start_span . $td['plain'] . $stop_span, $plain);

            $identifiers[] = $mid;
        }

        if (preg_match('#<img[^>]* src="[\.\/]+img/include.gif"[^>]*>#i', $plain) || preg_match('/<maska/i', $plain) || preg_match('/<mask/i', $plain) ) {
            $plain = $this->include_plain($plain, $mode, 1);
        }

        return $plain;
    }

    public function str2img($txt, $filename, $fontsize, $fontface, $posx, $posy, $color, $wrap, $width, $height, $time2compare)
    {
        $session = Bootstrap::$main->session();
        $destfile = $session['uimages_path'] . "/$filename";
        if (!file_exists(dirname($destfile))) mkdir(dirname($destfile), 0755, true);

        $filetime = file_exists($destfile) ? filemtime($destfile) : 0;

        //if ($filetime<$time2compare) return $filename;
        $ttf = new Ttf($session['uimages_path'] . '/', dirname($filename) . '/', $width, $height);
        $ttf->Transparency();
        $ttf->Crop();
        $ttf->WrapAt("|");
        $ttf->AddText($txt, $fontsize, $fontface, $posx, $posy, $color, $wrap);
        $ttf->Save(basename($filename));

        return $filename;
    }

    function str_to_url($s, $case = 0, $dots=false)
    {
        $acc = 'É	Ê	Ë	š	Ì	Í	ƒ	œ	µ	Î	Ï	ž	Ð	Ÿ	Ñ	Ò	Ó	Ô	Š	£	Õ	Ö	Œ	¥	Ø	Ž	§	À	Ù	Á	Ú	Â	Û	Ã	Ü	Ä	Ý	';
        $str = 'E	E	E	s	I	I	f	o	m	I	I	z	D	Y	N	O	O	O	S	L	O	O	O	Y	O	Z	S	A	U	A	U	A	U	A	U	A	Y	';

        $acc .= 'Å	Æ	ß	Ç	à	È	á	â	û	Ĕ	ĭ	ņ	ş	Ÿ	ã	ü	ĕ	Į	Ň	Š	Ź	ä	ý	Ė	į	ň	š	ź	å	þ	ė	İ	ŉ	Ţ	Ż	æ	ÿ	';
        $str .= 'A	A	S	C	a	E	a	a	u	E	i	n	s	Y	a	u	e	I	N	S	Z	a	y	E	i	n	s	z	a	p	e	I	n	T	Z	a	y	';

        $acc .= 'Ę	ı	Ŋ	ţ	ż	ç	Ā	ę	Ĳ	ŋ	Ť	Ž	è	ā	Ě	ĳ	Ō	ť	ž	é	Ă	ě	Ĵ	ō	Ŧ	ſ	ê	ă	Ĝ	ĵ	Ŏ	ŧ	ë	Ą	ĝ	Ķ	ŏ	';
        $str .= 'E	l	n	t	z	c	A	e	I	n	T	Z	e	a	E	i	O	t	z	e	A	e	J	o	T	i	e	a	G	j	O	t	e	A	g	K	o	';

        $acc .= 'Ũ	ì	ą	Ğ	ķ	Ő	ũ	í	Ć	ğ	ĸ	ő	Ū	î	ć	Ġ	Ĺ	Œ	ū	ï	Ĉ	ġ	ĺ	œ	Ŭ	ð	ĉ	Ģ	Ļ	Ŕ	ŭ	ñ	Ċ	ģ	ļ	ŕ	Ů	';
        $str .= 'U	i	a	G	k	O	u	i	C	g	k	o	U	i	c	G	L	O	u	i	C	g	l	o	U	o	c	G	L	R	u	n	C	g	l	r	U	';

        $acc .= 'ò	ċ	Ĥ	Ľ	Ŗ	ů	ó	Č	ĥ	ľ	ŗ	Ű	ô	č	Ħ	Ŀ	Ř	ű	õ	Ď	ħ	ŀ	ř	Ų	ö	ď	Ĩ	Ł	Ś	ų	Đ	ĩ	ł	ś	Ŵ	ø	đ	';
        $str .= 'o	c	H	L	R	u	o	C	h	l	r	U	o	c	H	L	R	u	o	D	h	l	r	U	o	d	I	L	S	c	D	i	l	s	W	o	d	';

        $acc .= 'Ī	Ń	Ŝ	ŵ	ù	Ē	ī	ń	ŝ	Ŷ	Ə	ú	ē	Ĭ	Ņ	Ş	ŷ';
        $str .= 'I	N	S	w	u	E	i	n	s	Y	e	u	e	I	N	S	y';

        $acc .= 'Б	б	В	в	Г	г	Д	д	Ё	ё	Ж	ж	З	з	И	и	Й	й	К	к	Л	л	М	м	Н	н	П	п	О	о	Р	р	С	с	Т	т	У	у	Ф	ф	Х	х	Ц	ц	Ч	ч	Ш	ш	Щ	щ	Ъ	Ы	ы	Ь	Э	э	Ю	ю	Я	я';
        $str .= 'B	b	W	w	G	g	D	d	Yo	yo	Z	z	Z	z	I	i	N	n	K	k	L	l	M	m	H	h	P	p	O	o	P	p	S	s	T	t	U	u	f	F	Ch	h	C	c	C	c	Sz	sz	S	s	-	Y	y	-	E	e	Iu	iu	Ia	ia';


	$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C', 
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH', 
		'ß' => 'ss', 
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 
		'ÿ' => 'y',
 
		// Latin symbols
		'©' => '(c)',
 
		// Greek
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y',
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
 
		// Turkish
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 
 
		// Russian
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',
 
		// Ukrainian
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
 
		// Czech
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U', 
		'Ž' => 'Z', 
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z', 
 
		// Polish
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
		'Ż' => 'Z', 
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',
 
		// Latvian
		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N', 
		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z'
	);




        
        
        //$out = str_replace(explode("\t", $acc), explode("\t", $str), $s);
        
        $out = str_replace(array_keys($char_map), $char_map, $s);
        $out = str_replace(' ', '-', trim($out));
        
        
        for ($i=0;$i<strlen($out)-1;$i++) {
            if ((ord($out[$i])==216 || ord($out[$i])==217) && ord($out[$i+1])>127)
            {
                $ar = new I18N_Arabic('Transliteration');    
                $out = trim($ar->ar2en($out));
                break;
            }
        }
        
        
        
        $out=str_replace('/','-',$out);
        if ($dots) $out=str_replace('.','-',$out);
        $out=preg_replace('#[^0-9a-z\/\-\._]#i','-',$out);
        $out=preg_replace('#-+#','-',$out);

        while (strlen($out)>3 && $out[0]=='-') $out=substr($out,1);
	while (strlen($out)>3 && substr($out,-1)=='-') $out=substr($out,0,strlen($out)-1);
        
        if ($case == -1) {
            return strtolower($out);
        } else {
            if ($case == 1) {
                return strtoupper($out);
            } else {
                return ($out);
            }
        }
    }

    /**
     * @param string $section
     * @param array $page
     * @return array
     */
    public function get_user_variables($section, array $page)
    {
        $config = Bootstrap::$main->getConfig();
        $d_xml_a = unserialize(base64_decode($page['d_xml']));
        $d_xml = array();
        if (isset($config[$section]['user']) && is_array($config[$section]['user'])) {
            foreach ($config[$section]['user'] as $type => $data) {
                if ($type == '*' || $type == $page['type']) {
                    foreach ($data as $name => $def) {
                        $def['name'] = $name;
                        $def['value'] = isset($d_xml_a[$name]) ? $d_xml_a[$name] : '';
                        $def['html'] = $this->get_html_from_xml($def);

                        $d_xml[] = $def;
                    }
                }
            }
        }

//        die('<pre>' . print_r($d_xml, 1) . PHP_EOL);
        return $d_xml;
    }

    /**
     * @param array $def
     * @return string
     */
    public function get_html_from_xml(array $def)
    {
        
        
        if (!isset($def['values'])) {
            $ta = isset($def['type']) && $def['type']=='textarea';
            
            $html = $ta? '<textarea':'<input type="text"';
            $html .= ' name="d_xml[' . $def['name'] . ']"';
            if (isset($def['style'])) {
                $html .= ' style="' . $def['style'] . '"';
            }
            $html .= $ta ? '>'.$def['value'] :' value="' . $def['value'] . '"';
            
            $html .= $ta ? '</textarea>' : ' />';

            return $html;
        }

        if ($def['values'] == 'calendar') {
            $html = '<input type="text" datepicker="1"';
            $html .= ' name="d_xml[' . $def['name'] . ']"';
            $html .= ' value="' . $def['value'] . '"';
            if (isset($def['style'])) {
                $html .= ' style="' . $def['style'] . '"';
            }
            $html .= ' />';

            return $html;
        }
        
        if ($def['values'] == '0|1') {
            $html  = '<input type="hidden" name="d_xml[' . $def['name'] . ']" value="0" />';
            $html .= '<input type="checkbox"';
            $html .= ' name="d_xml[' . $def['name'] . ']"';
            $html .= ' value="1"';
            if (isset($def['style'])) {
                $html .= ' style="' . $def['style'] . '"';
            }
            if ($def['value']) {
                $html .= ' checked';
            }
            $html .= ' />';

            return $html;
        }

        $html = '<select';
        $html .= ' name="d_xml[' . $def['name'] . ']"';
        if (isset($def['style'])) {
            $html .= ' style="' . $def['style'] . '"';
        }
        $html .= '>';
        foreach (explode('|', $def['values']) as $opt) {
            $html .= '<option value="' . $opt . '"';
            if ($opt == $def['value']) {
                $html .= ' selected';
            }
            $html .= '>' . $opt . '</option>';
        }
        $html .= '</select>';

        return $html;
    }



    public function trans($label)
    {
        return Tools::translate($label);
    }
    
    
    public function new_sid($sid)
    {
        $new_sid=Bootstrap::$main->session('new_sid');
        if ($new_sid==$sid)
        {
            Bootstrap::$main->session('new_sid',0);
            return true;
        }
        
        return false;
    }
}
