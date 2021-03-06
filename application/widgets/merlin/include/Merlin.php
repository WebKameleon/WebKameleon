<?php



// http://docu.mdsws.merlinx.pl
// http://docu.mdsws.merlinx.pl/data:fields:names:obj_xattributes uzytkownik ecco:3CC0
// http://docu.mdsws.merlinx.pl/data:fields:names:filters
// obrazki: http://docu.mdsws.merlinx.pl/apdx:data


class MERLIN
{
    private $user,$pass;
    private $url='http://mdswsb.merlinx.pl/V3/';
    private $_ver=3;

    private $_section_map=array(
                                'autosuggestV1'=>'citySearchByName,citySearchByCoords,airportSearchByCoords,airportSearchByCity,airportSearchByIata,airportSearchByName',
                                'bookingstatusV3'=>'bookingstatus',
                                'bookV3'=>'check,book',
                                'checkavailV3'=>'checkavail',
                                'confirmationprintV3'=>'confirmationprint',
                                'dataV3'=>'regions,skiregions,filters,groups,offers,details,check_external_flight_wait,check_external_flight_nowait,check_external_hotel_wait,check_external_hotel_nowait,check_external_hotel_nowait',
                                'externalservicesV1'=>'L,LH,D,S,O,B,F',
                                'extradataV3'=>'extradata',
                                'lukasV1'=>'instalmentlist,instalment',
                                'optionconfirmV3'=>'optionconfirm',
                                'exthotelsdetailsV1'=>'ARI,AHI'
    );
    protected $section_map;
                               
                      
    private $operator_code;
    private $filters;
    private $hotels;
    public $debug=[];
    

    public function __construct($user,$pass,$operators='')
    {        
        

        $this->section_map=array();
        foreach($this->_section_map AS $section=>$types)
        {
            foreach (explode(',',$types) AS $type)
            {
                $this->section_map[$type]=$section;
            }
        }


        $this->pass=$pass;
        $this->user=$user;

        $this->operator_code=$operators;
        
    }


    protected function session($k,$v=null)
    {
        if (class_exists('Bootstrap')){
            if (!is_null($v)) Bootstrap::$main->session('merlin.'.$k,$v);
            return Bootstrap::$main->session('merlin.'.$k);
        }
        
        if (!is_null($v)) $_SESSION['merlin.'.$k]=$v;
        if (isset($_SESSION['merlin.'.$k])) return $_SESSION['merlin.'.$k];
        return false;
    }

    
    protected function getUrl($type)
    {
        $urls = array('http://mdsws.merlinx.pl/','http://mdswsb.merlinx.pl/');
        $random=rand(0,count($urls)-1);
        
        if ($this->_ver==2 || empty($this->section_map[$type])) return $urls[$random].'V2.3.1/';
        
        $section=$this->section_map[$type];
        
        return $urls[$random].$section.'/';
    }
    
    protected function debug($obj=null, $debug='debug', $puke=0, $color='red')
    {
        $plus='';
        while(isset($this->debug[$debug.$plus])) $plus=$plus+1;
        $this->debug[$debug.$plus]=$obj;
    }
    
    protected function post_xml(&$xml,$type,$subtype='')
    {



        $url=$this->getUrl($type);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST,   1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS, preg_replace("/>[ \t\r\n]+</",'><',$xml) );

        $response = curl_exec($ch);
        curl_close($ch);
        $debug_xml=$xml;
        $debug_xml=preg_replace('/<pass>[^>]*<\/pass>/','<pass>***</pass>',$debug_xml);
        $debug_xml=preg_replace("~(</[^>]*>)~","\\1\n",$debug_xml);
        
        $debug_string=htmlspecialchars($debug_xml);

        $debug_string='POST '.$url.'<hr size="1"/>'.$debug_string;

        if (!strlen($response))
        {

            $this->debug($debug_string,'mds XML - ERROR - empty resp');
            return false;
        }

        $this->debug($debug_string.'<hr size="1">'.htmlspecialchars($response),'mds XML '.$type.$subtype);

        $ret=$this->_xml2arr($response);
        $this->debug($ret,'mds XML object '.$type.$subtype);
        return $ret;
    }
    
    protected function _xml2arr($response)
    {
        $ret=simplexml_load_string($response);
        $ret=json_decode(json_encode($ret),true);
        
        return $ret;
    }

    
    private function getAttributes(&$obj)
    {
        $wynik=array();
        
        foreach ($obj['@attributes'] AS $k=>$v)
        {
            $wynik["$k"]="$v";
        }
        
        return $wynik;
    }
    
    private function getAttribute(&$obj,$attr)
    {

        foreach ($obj['@attributes'] AS $k=>$v)
        {
            if ($k==$attr) return "$v";
        }
    }

    private function getIdAttributes(&$obj,$path,$id='id',$emptys=false)
    {
        $result=array();

        if (!is_array($obj)) return $result;

        foreach ($obj[$path] AS $item)
        {
            $v=$this->getAttribute($item,$id);
            if (!$emptys && !strlen("$v")) continue;
            $result[]="$v";
        }


        return $result;
    }
    
    private function _obj2xml($array,$name,$node=null)
    {
        if (is_null($node))
        {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.$name.'/>');
        }
        else
        {
            $xml=$node->addChild($name);
        }
        
        foreach ($array AS $k=>$v)
        {
            if (is_array($v))
            {
                $this->_obj2xml($v,$k,$xml);
            }
            else
            {
                $xml->addChild($k,$v);
            }
        }
        
        return $xml->asXML();
           
    }


    private function request($type,$conditions,$more=null)
    {

        $mds['auth']['login']=$this->user;
        $mds['auth']['pass']=$this->pass;
        $mds['request']['type']=$type;

        if ($this->operator_code && !isset($conditions['ofr_tourOp']) ) $conditions['ofr_tourOp']=$this->operator_code;

        if (isset($conditions['obj_xCityFts']))
        {
            // OR:
            //$conditions['obj_xCityFts']=str_replace('|',',',$conditions['obj_xCityFts']);

            // AND:
            $conditions['obj_xCityFts']='|'.str_replace('|','| |',$conditions['obj_xCityFts']).'|';
        }

        $mds['request']['conditions']=$conditions;

        if (is_array($more)) foreach ($more AS $k=>$v) $mds['request'][$k]=$v;


        
        $xml=$this->_obj2xml($mds,'mds');



        return $xml;
    }

    public function type_convert($type)
    {
        $type_convert=array('WS'=>'F','LO'=>'NF,OW','DW'=>'H');

        if (strlen($type_convert[$type])) $type=$type_convert[$type];

        return $type;
    }

    public function getFilters($cond=array(),$what='*')
    {
        if ($this->operator_code) $cond['ofr_tourOp']=$this->operator_code;
    
        $cond['filters']='obj_xServiceId,trp_depName,trp_durationM,ofr_catalog,obj_category,ofr_catalog,ofr_type,obj_xAttributes,trp_depDate,obj_city,obj_xCode,ofr_tourOp';

        if (!isset($cond['trp_retDate'])) $cond['trp_retDate']=date('Ymd',time()+365*24*3600);

        if (isset($cond['obj_code']) || isset($cond['obj_xCode']) ) $cond['filters'].=',obj_room';

        if (isset($cond['ofr_type']) && $cond['ofr_type']=='NF,OW') $cond['filters']='trp_depName,trp_depDate';

        $md5=md5(serialize($cond));


        if (!isset($this->filters[$md5]))
        {
            $this->filters[$md5]=$this->session('filters.'.$md5);
        }
        

        if (!$this->filters[$md5])
        {
            $xml=$this->request('filters',$cond);
            $this->filters[$md5]=$this->post_xml($xml,'filters');
            if (isset($this->filters[$md5]['fdef'])) $this->session('filters.'.$md5,$this->filters[$md5]);

        }

        $result=array();

        
        
        foreach($this->filters[$md5]['fdef'] AS $fdef)
        {
            if (isset($fdef['@attributes']['id']) && $fdef['@attributes']['id']==$what)
            {
                if (!is_array($fdef['f'])) continue;
                if (count($fdef['f']) && !isset($fdef['f'][0])) $fdef['f']=array($fdef['f']);
                foreach ($fdef['f'] AS $f)
                {
                
                    if (!isset($f['@attributes'])) mydie($fdef);
                    foreach ($f['@attributes'] AS $id=>$val)
                    {
                        if (count($f['@attributes'])==1)
                        {
                            if ($id=='id') $result[]="$val";
                        }
                        else
                        {
                            if ($id=='id') $key="$val";
                            if ($id=='v') $result[$key]="$val";
                        }
                    }
                }
            }
        }
        

        return $result;
    }
    
    protected function time2str($d)
    {
        $t=strtotime($d);
        if (!$t) return '';
        return date('Ymd',$t);
    }

    public function getOfferOnToken($token)
    {
        $o=$this->getOffers(null,null,null,null,null,$token);
        if (isset($o['result'][0])) return $o['result'][0];
        return null;
    }
    
    protected function offer2cond($offer)
    {
        $cond=[];
        
        if (isset($offer['hotel']) && $offer['hotel'])
        {
            $cond['obj_code']= $offer['hotel'];
        }

        if (isset($offer['xhotel']) && $offer['xhotel'])
        {
            $cond['obj_xCode']= $offer['xhotel'];
        }        
        
        if (isset($offer['op']) && $offer['op'])
        {
            $cond['ofr_tourOp']= $offer['op'];
        }

        if (isset($offer['dep']) && $offer['dep'])
        {
            $cond['trp_depName']= $offer['dep'];
        }
        
        if (isset($offer['adt']) && $offer['adt'])
        {
            $cond['par_adt']= $offer['adt'];
        }
        if (isset($offer['chd']) )
        {
            $cond['par_chd']= $offer['chd'];
        }
        
        /*
        if (isset($offer['o_kod_hotelu'])) $cond['obj_code']= $offer['o_kod_hotelu'];
        if (isset($offer['o_kod_pokoju'])) $cond['obj_room']= $offer['o_kod_pokoju'];
        if (isset($offer['o_wyzywienie']))
        {
            if ($offer['o_wyzywienie']+0==0) $cond['obj_service']= $offer['o_wyzywienie'];
            else $cond['obj_xServiceId']= $offer['o_wyzywienie'];
        }

        $cond['par_adt']= $offer['rodzinka'][0];
        $rodzinka=$offer['rodzinka'];
        for ($i=1;$i<count($rodzinka);$i++)
        {
            if ($rodzinka[$i]<2) $cond['par_inf']++;
            else
            {
                $cond['par_chd']++;
                if (isset($cond['par_chdAge'])) $cond['par_chdAge'].=',';
                $cond['par_chdAge'].=$rodzinka[$i];
            }
        }

        if (isset($offer['o_cena_max'])) $cond['maxPrice']=$offer['o_cena_max'];
        if (isset($offer['o_cena_min'])) $cond['minPrice']=$offer['o_cena_min'];


        if (isset($offer['o_dni'])) {
            if (is_array($offer['o_dni']))
            {
                $cond['trp_duration']=implode(':',$offer['o_dni']);
                $offer['o_dni']=$offer['o_dni'][0]+0;
            }
    	    elseif ($offer['o_dni']<>0) $cond['trp_duration']= $offer['o_dni'];
    	}
       

        */
       
        $zarok=$this->time2str(date('Y-m-d',time()+365*24*3600));
        if (isset($offer['from']) && $offer['from'])
        {
            $cond['trp_depDate']=$this->time2str($offer['from']).':'.$zarok;
        }
        
        if (isset($offer['from']) && isset($offer['fromto']) && $offer['fromto'] && $offer['from'])
        {
            $cond['trp_depDate']=$this->time2str($offer['from']).':'.$this->time2str($offer['fromto']);
        }
        
        if (isset($offer['to']) && $offer['to'])
        {
            $cond['trp_retDate']=$this->time2str($offer['to']);
        } 
 
        if (isset($cond['trp_depDate']) && !isset($cond['trp_retDate']))
        {
            $cond['trp_retDate']=$this->time2str($offer['from']).':'.$zarok;
        }

        if (isset($offer['dest']) && $offer['dest'])
        {

            $cond['trp_destination']=$offer['dest'];
	    if ($cond['trp_destination'][strlen($cond['trp_destination'])-1]==',') $cond['trp_destination']=substr($cond['trp_destination'],0,strlen($cond['trp_destination'])-1); 
        }
        
        /*
        if (isset($offer['o_kod_miasta']))
        {
            $cond['trp_destination']=$offer['o_kod_miasta'];
        }

        if (isset($offer['o_atrybuty']))
        {
            $cond=array_merge($cond,$this->getCondOnAttributeArray($offer['o_atrybuty']));
        }

        if (isset($offer['o_kategoria']))
        {
            $offer['o_kategoria']=str_replace(')','',$offer['o_kategoria']);
            $offer['o_kategoria']=str_replace('(','',$offer['o_kategoria']);

            $kat=explode(',',$offer['o_kategoria']);

            if (count($kat)==1) $cond['obj_category']=10*$offer['o_kategoria'];
            else
            {
                $cond['obj_category']=(10*$kat[0]).':'.(10*$kat[count($kat)-1]);
            }

        }

        $cond['ofr_type']=$this->type_convert($type);

        */

        return $cond;
    }
    
    
    public function getOffers($offer=[],$type='',$order='',$limit=10,$offset=0,$token='',$search4otherDates=false)
    {
        $cond=$this->offer2cond($offer);
        $cond['calc_found']=1000;
        $cond['limit_count']=$limit;
        $cond['limit_from']=$offset+1;

        $type='offers';


        if ($order) $cond['order_by']=$this->orderOnArray($order);

        if (strlen($token))
        {
            $cond=array('ofr_id'=>$token);
            $type='details';
        }
        

        $xml2=$this->request($type,$cond);

        $xml_response = $this->post_xml($xml2,$type);

        if ($xml_response['count']==0 && !strlen($token) && $limit>5 && $search4otherDates)
        {
            $cond2=$cond;
            unset($cond2['trp_depDate']);

            $cond2['order_by']='trp_depDate';
            $cond2['limit_count']=1;
            $cond2['limit_from']=1;

            $xml2=$this->request('offers',$cond2);
            $ofrs = $this->post_xml($xml2,$type);
            $ofr=$this->convertOffers($ofrs);
            if (isset($ofr[0]['o_data']))
            {
                $cond['trp_depDate']=date('Ymd',strtotime($ofr[0]['o_data']));

                if (isset($offer['o_data_przylotu_pow']))
                {
                    $diff=round((strtotime($offer['o_data_przylotu_pow'])-strtotime($offer['o_data_wylotu']))/(24*3600));
                    if ($diff>0) $cond['trp_depDate'].=':'.date('Ymd',strtotime($ofr[0]['o_data'])+24*3600*$diff);
                }

                $xml2=$this->request('offers',$cond);
                $xml_response = $this->post_xml($xml2,'offers','2');

            }
        }


        return $this->convertOffers($xml_response);

    }
    
    protected function xAttr2array($a)
    {
        $token='attr.'.$a;
        $attr=$this->session($token);
        if ($attr) return $attr;
        $attr=[];
        for ($e=1;$e<70;$e++)
        {
            if ( ($a+0) & pow(2,$e-1) ) $attr[]='m_'.$e;
        }
        return $this->session($token,$attr);
    }
    
    public function orderOnArray($order) {
        $cond['order_by']='';
        
        if (is_array($order)) foreach ($order AS $o)
        {
            $order_by='';

            if (strstr($o,'cena')) $order_by='ofr_price';
            if (strstr($o,'kraj')) $order_by='obj_country';
            if (strstr($o,'data')) $order_by='trp_depDate';
            if (strstr($o,'wylo')) $order_by='trp_depName';
            if (strstr($o,'region')) $order_by='obj_region';
            if (strstr($o,'miasto')) $order_by='obj_city';
            if (strstr($o,'nazwa')) $order_by='obj_name';
            

            if (strstr(strtolower($o),'desc'))
            {
                $order_by='-'.$order_by;
                str_replace(',','-,',$order_by);
            }
            if (strlen($order_by))
            {
                if (strlen($cond['order_by'])) $cond['order_by'].=',';
                $cond['order_by'].=$order_by;
            }

        }
        
        return $cond['order_by'];
    }
    

    public function merlinDate($d)
    {
        return substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6);
    }

    public function hotelInfo($op,$htlCode)
    {
        $token='hotel-'.$op.'-'.$htlCode;
        
        $ret=$this->session($token);
        if ($ret) return $ret;
        
        $url='http://data2.merlinx.pl/index.php?login='.$this->user.'&password='.$this->pass.'&tourOp='.$op.'&htlCode='.$htlCode;
        $response=file_get_contents($url);
        $hotel=$this->_xml2arr($response);
        
        $this->debug($hotel,'Hotel info: '.$htlCode);
        
        $ret=[];
        
        if (isset($hotel['hotelData']['images']['thumb']))
            $ret['thumb']=$hotel['hotelData']['images']['thumb'];
        if (isset($hotel['hotelData']['images']['pictures']['picture']))
            $ret['photos']=$hotel['hotelData']['images']['pictures']['picture'];
            
        if (isset($hotel['hotelData']['texts']['text']))
            $ret['desc']=$hotel['hotelData']['texts']['text'];
        
        return $this->session($token,$ret);
    }
    
    public function convertOffers($offers,$path='ofr')
    {
    
        $ret=[];
        if (isset($offers['count']))
        {
            $ret['count']=$offers['count'];
            if ($ret['count']==1 && !isset($offers[$path][0])) $offers[$path]=array($offers[$path]);
        }
        
        $ret['result']=[];

        if (is_array($offers[$path]) && count($offers[$path]) && !isset($offers[$path][0])) $offers[$path]=array($offers[$path]);
        
        if (is_array($offers[$path])) foreach ($offers[$path] AS $resp)
        {
    
            if (isset($resp['ofr'])) $resp=$resp['ofr'];
            
            $rec=$resp['@attributes'];
            $rec['startDate']=array();
            $rec['obj']=$resp['obj']['@attributes'];
            $rec['trp']=$resp['trp']['@attributes'];
            
            
            if (isset($rec['obj']['xAttributes']) && $rec['obj']['xAttributes']) $rec['obj']['attributes']=$this->xAttr2array($rec['obj']['xAttributes']);

            $rec['obj']['info']=$this->hotelInfo($rec['tourOp'],$rec['obj']['code']);   
            
            $rec['startDate']['YYYYMMDD']=$this->merlinDate($rec['trp']['depDate']);
            $rec['startDate']['DDMMYYYY']=date('d-m-Y',strtotime($rec['startDate']['YYYYMMDD']));
            $rec['startDate']['MMDDYYYY']=date('m/d/Y',strtotime($rec['startDate']['YYYYMMDD']));
            
            $ret['result'][]=$rec;
            
        }
        
        return $ret;        
        
        $result=array();

        if (is_object($offers->$path)) foreach ($offers->xpath($path) AS $offer)
        {
            
            if ($path=='grp') $offer=$offer->ofr;
            

            $r=array();
            $r['o_id']=$this->getAttribute($offer,'id');
            $r['o_kod_katalogu']=$this->getAttribute($offer,'catalog');
            $r['o_kod_destynacji']=$this->getAttribute($offer->obj,'desCode');
            $r['o_kod_hotelu']=$this->getAttribute($offer->obj,'code');
            $r['o_kod_pokoju']=$this->getAttribute($offer->obj,'room');
            $r['o_nazwa']=$this->getAttribute($offer->obj,'name');
            $r['o_xnazwa']=$this->getAttribute($offer->obj,'xName');
            $r['o_dni']=$this->getAttribute($offer->trp,'durationM');
            $r['o_data']=$this->merlinDate($this->getAttribute($offer->trp,'depDate'));
	    $r['o_linia_lot'] = preg_replace('/[0-9]/','', $this->getAttribute($offer->trp,'carrierCode'));
            $r['o_lot']=$this->getAttribute($offer->trp,'carrierCode');
            $r['o_wylot']=$this->getAttribute($offer->trp,'depCode');
            $r['o_przylot']=$this->getAttribute($offer->trp,'desCode');
            $r['o_wylot_pow']=$this->getAttribute($offer->trp,'rDepCode');
            $r['o_przylot_pow']=$this->getAttribute($offer->trp,'rDesCode');
            $r['o_data_wylotu']=$this->merlinDate($this->getAttribute($offer->trp,'depDate'));
            $r['o_godz_wylotu']=substr($this->merlinDate($this->getAttribute($offer->trp,'depTime')), 0, 2).":".substr($this->merlinDate($this->getAttribute($offer->trp,'depTime')), 2, 2).":00";
            $r['o_data_przylotu']=$this->merlinDate($this->getAttribute($offer->trp,'desDate'));
            $r['o_godz_przylotu']=substr($this->merlinDate($this->getAttribute($offer->trp,'arrTime')), 0, 2).":".substr($this->merlinDate($this->getAttribute($offer->trp,'arrTime')), 2, 2).":00";
            $r['o_data_wylotu_pow']=$this->merlinDate($this->getAttribute($offer->trp,'rDepDate'));
            $r['o_godz_wylotu_pow ']=substr($this->merlinDate($this->getAttribute($offer->trp,'rDepTime')), 0, 2).":".substr($this->merlinDate($this->getAttribute($offer->trp,'rDepTime')), 2, 2).":00";
            $r['o_data_przylotu_pow']=$this->merlinDate($this->getAttribute($offer->trp,'rDesDate'));
	    $r['o_godz_przylotu_pow']=substr($this->merlinDate($this->getAttribute($offer->trp,'rArrTime')), 0, 2).":".substr($this->merlinDate($this->getAttribute($offer->trp,'rArrTime')), 2, 2).":00";
	    $r['o_kategoria']="";
            $r['o_wyzywienie']=$this->getAttribute($offer->obj,'service');
            $r['o_osoby']=$this->getAttribute($offer->obj,'maxPax');
            $r['o_osoby_full']=$this->getAttribute($offer->obj,'minPax');
            $r['o_min_fam']=$this->getAttribute($offer->obj,'minAdt');
            $r['o_max_dor']=$this->getAttribute($offer->obj,'maxAdt');
            $r['o_cena']=$this->getAttribute($offer,'operPrice');
            $r['o_waluta']=$this->getAttribute($offer,'operCurr');
            $r['o_token']=$this->getAttribute($offer,'id');
            $r['o_opis']="";
            $r['o_kod_oferty']=$this->getAttribute($offer,'type')."-".$this->getAttribute($offer->obj,'code')."-".$this->getAttribute($offer->obj,'room')."-".$r['o_data']."-".$r['o_dni']."-".$r['o_wylot']."-".$r['o_wyzywienie']."-".$r['o_osoby']."-".$r['o_osoby_full']."-".$r['o_min_fam']."-".$r['o_max_dor'];
            $r['o_data_m']="";
            $r['o_lot_tam_kod']=$this->getAttribute($offer->trp,'flightCode');
            $r['o_lot_pow_kod']=$this->getAttribute($offer->trp,'rFlightCode');

            $r['o_kod_miasta']=$this->getAttribute($offer->obj,'city');
            $r['o_token_lot']=$r['o_data_wylotu']."-".$r['o_wylot']."-".$r['o_przylot']."-".$r['o_lot']."-1";
            $r['o_token_lot_pow']=$r['o_data_wylotu_pow']."-".$r['o_wylot_pow']."-".$r['o_przylot_pow']."-".$this->getAttribute($offer->trp,'rCarrierCode')."-1";
            $r['o_token_dni']=$this->getAttribute($offer->obj,'code').".".$this->getAttribute($offer->obj,'room').".".$r['o_data'].".".$r['o_wyzywienie'].".".$r['o_osoby'].".".$r['o_osoby_full'].".".$r['o_min_fam'].".".$r['o_max_dor'];
            $r['o_lot_pow']=$this->getAttribute($offer->trp,'rCarrierCode');
            $r['o_online']="1";
            $r['o_kod_katalogu2']=$this->getAttribute($offer,'catalog');
            $r['o_atrybuty']=$this->getAttribute($offer->obj,'xCity');
            if ($r['o_atrybuty'][0]=='|') $r['o_atrybuty']=substr($r['o_atrybuty'],1);
            if ($r['o_atrybuty'][strlen($r['o_atrybuty'])-1]=='|') $r['o_atrybuty']=substr($r['o_atrybuty'],0,strlen($r['o_atrybuty'])-1);
            $r['o_atrybuty']=preg_replace('/[|]+/','|',$r['o_atrybuty']);
            $r['o_atrybuty']=explode('|',$r['o_atrybuty']);

            /*
            if (!is_array($this->hotels[$r['o_kod_hotelu']]))
            {
                $typ=strlen($r['o_wylot'])?'WS':'DW';
                $this->hotels[$r['o_kod_hotelu']]=$this->operator->rekord('hotel',"h_typ='$typ' AND h_kod_hotelu='".$r['o_kod_hotelu']."'");
            }
            */
            
            if (is_array($this->hotels[$r['o_kod_hotelu']]) && count($this->hotels[$r['o_kod_hotelu']]) )
            {
                $r=array_merge($r,$this->hotels[$r['o_kod_hotelu']]);

                if ($this->hotels[$r['o_kod_hotelu']]['h_merlin_id'] <= 0 )
                {
                    $this->hotels[$r['o_kod_hotelu']]['h_merlin_id']=$this->getAttribute($offer->obj,'xCode');
                    //$this->operator->zapisz('hotel',$this->hotels[$r['o_kod_hotelu']]['h_typ'],$this->hotels[$r['o_kod_hotelu']]);
                }


                if (!$this->hotels[$r['o_kod_hotelu']]['h_google']
                    && ($xLat=$this->getAttribute($offer->obj,'xLat'))
                    && ($xLong=$this->getAttribute($offer->obj,'xLong'))
                )
                {
                    $this->hotels[$r['o_kod_hotelu']]['h_google']="$xLat,$xLong,14";
                    //if ($xLat+0!=200) $this->operator->zapisz('hotel',$this->hotels[$r['o_kod_hotelu']]['h_typ'],$this->hotels[$r['o_kod_hotelu']]);
                }
            }
            $r['x_kod_hotelu']=$this->getAttribute($offer->obj,'xCode');

            $a=$this->getAttribute($offer->obj,'xAttributes');

            /*
            for ($e=1;$e<70;$e++)
            {
                if ( ($a+0) & pow(2,$e-1) ) $r['m_atrybuty'][]=$this->operator->atrybut("m_$e",'M');
            }
            */

            if (!strlen($r['h_kod_hotelu'])) $r['h_kod_hotelu']=$this->getAttribute($offer->obj,'code');
            if (!strlen($r['h_kod_destynacji'])) $r['h_kod_destynacji']=$this->getAttribute($offer->trp,'desCode');
            if (!strlen($r['h_nazwa'])) $r['h_nazwa']=$this->getAttribute($offer->obj,'name');
	    if (!strlen($r['h_kraj'])) $r['h_kraj']=$this->getAttribute($offer->obj,'country');
            if (!strlen($r['h_region'])) $r['h_region']=$this->getAttribute($offer->obj,'region');
            if (!strlen($r['h_miasto'])) $r['h_miasto']=$this->getAttribute($offer->obj,'city');
            if (!strlen($r['h_google_latitude'])) $r['h_google_latitude']=$this->getAttribute($offer->obj, 'xLat');
            if (!strlen($r['h_google_longitude'])) $r['h_google_longitude']=$this->getAttribute($offer->obj, 'xLong');
            if (!strlen($r['h_token'])) $r['h_token']=$r['h_typ'].".".$r['h_kod_hotelu'];


            $r['rodzina']="";
            $r['razem_ofert']=0+$offers->count;
            $r['lp']="1";
            $r['cena_rodzina']="";
            $r['cena_dziecko']="";
            $r['rezerwuj']="1";

            

            //$this->operator->label('wyzyw',$this->getAttribute($offer->obj,'xServiceId'),$this->getAttribute($offer->obj,'serviceDesc'));
            //$this->operator->label('wyzyw',$this->getAttribute($offer->obj,'service'),$this->getAttribute($offer->obj,'serviceDesc'));
            //$this->operator->label('pokoj',$this->getAttribute($offer->obj,'room'),$this->getAttribute($offer->obj,'roomDesc'));
            $result[]=$r;
        }


        return $result;
    }

    public function getCatalogs($type='WS')
    {
        $cond=array();
        $cond['ofr_type']=$this->type_convert($type);


        //print_r($cond);
        $catalogs=$this->getFilters($cond,'ofr_catalog');

        return $catalogs;
    }

    
    protected function unzip($f)
    {
        $zip = new ZipArchive;
        $file=tempnam (sys_get_temp_dir(),'merlin');
        file_put_contents($file,file_get_contents($f));
        $zip->open($file);
        $csv=explode("\n",$zip->getFromIndex(0));
        $zip->close();
        $h=explode('";"',substr($csv[0],1,strlen($csv[0])-2));
        $result=array();
        for($i=1;$i<count($csv);$i++)
        {
            $line=explode('";"',substr($csv[$i],1,strlen($csv[$i])-2));
            $rec=array();
            foreach($line AS $k=>$v) if (strlen(trim($v))) $rec[$h[$k]]=$v;
            if (count($rec)) $result[]=$rec;
        }
        
        return $result;
    }
    
    public function getRegions($type=null,$limits=null)
    {
        $result=array();

        $cond=array();
        $cond['par_adt']=2;
        //$cond['ofr_type']=$this->type_convert($type);

        if (is_array($limits)) $cond=array_merge($cond,$limits);

        $token='reg.'.md5(serialize($cond));
        
        $r=$this->session($token);
        if ($r) return $r;
        
        $xml=$this->request('regions',$cond);

        $regions=$this->post_xml($xml,'regions');
        
        $rg=__DIR__.'/regions.json';
        
        if ( !file_exists($rg) ||  filemtime($rg)<time()-24*3600)
        {
            $rgns=$this->unzip('http://www.merlinx.pl/mdsws/regions_utf8.zip');
            file_put_contents($rg,json_encode($rgns));
        }
        else
        {
            $rgns=json_decode(file_get_contents($rg),true);
        }

        $result=(array)$regions;
        $wynik=array();
        foreach ($result['reg'] AS $r)
        {
            $rec=$r['@attributes'];
            $id=explode('_',$rec['id']);

            foreach ($rgns AS $region)
            {
                if ($region['country']=='YES' && $id[0]==$region['num'])
                {
                    $rec['country']=$region['region'];
                    $rec['country_code']=$region['num'];
                }
                if ($region['country']=='NO' && $id[1]==$region['num'])
                {
                    $rec['region']=$region['region'];
                }
            }
            
            $wynik[]=$rec;
        }
        
        return $this->session($token,$wynik);

    }

  

    public function getServicess($type,$hotel='')
    {
        $cond=array();
        $cond['ofr_type']=$this->type_convert($type);
        if (strlen($hotel)) $cond['obj_code']=$hotel;

        $services=$this->getFilters($cond,'obj_xServiceId');

        $result=array();
        foreach ($services AS $service)
        {
            $result[]=array('kod'=>$service,'wyzywienie'=>$service/*$this->operator->label('wyzyw',$service)*/);
        }
        return $result;
    }

    public function getDays($type,$hotel)
    {
        $cond=array();
        $cond['ofr_type']=$this->type_convert($type);
        if (strlen($hotel)) $cond['obj_code']=$hotel;

        $days=$this->getFilters($cond,'trp_durationM');
        $tmp=array('Dowolna');

        if (is_array($days)) sort($days);
	$days=array_merge($tmp,$days);
        $result=array();
        foreach ($days AS $day)
        {
            $result[]=array('o_dni'=>$day);
        }
        return $result;
    }





    public function getFlightDates($type,$dest='',$hotel='',$dep='')
    {
        $cond=array();
        $cond['ofr_type']=$this->type_convert($type);
        if (strlen($hotel)) $cond['obj_code']=$hotel;
        if (strlen($dest)) $cond['trp_destination']=$dest;
        if (strlen($dep)) $cond['trp_depName']=$dep;

        $days=$this->getFilters($cond,'trp_depDate');

        $result=array();
        foreach ($days AS $day)
        {
            $result[]=array('o_data_wylotu'=>date('d-m-Y',strtotime($day)));
        }
        return $result;

    }

    public function getRooms($type,$hotel)
    {
        $cond=array();
        $cond['ofr_type']=$this->type_convert($type);
        if (strlen($hotel)) $cond['obj_code']=$hotel;

        $rooms=$this->getFilters($cond,'obj_room');


        $result=array();
        foreach ($rooms AS $code=>$room)
        {
            $result[]=array('o_kod_pokoju'=>$code,'label'=>$room);
        }
        return $result;

    }

   
    
    public function getGrouped($params,$type='',$order='',$limit=10,$offset=0)
    {
        $cond=$this->offer2cond($params);
        $cond['calc_found']=1000;


        if (is_array($order)) {
            $cond['order_by']=$this->orderOnArray($order);
        }
        elseif (strstr($order,'cena') || !$order) $cond['order_by']='ofr_price';


        if ($limit)  $cond['limit_count']=$limit;
        if ($offset)  $cond['limit_from']=$offset+1;
        

            
        $hotels = $this->post_xml($this->request('groups',$cond),'groups');
        $hotels = $this->convertOffers($hotels,'grp');



        return $hotels;
    }

    private function getCondOnAttributeArray($a)
    {
        $cond=array();

        $xAttr=0;
        $xCity=array();
        foreach ($a AS $at)
        {
            //$at=$this->operator->konwertujAtrybut($at);
            if (is_integer($at)) $xAttr+=$at;
            elseif (strlen($at)) $xCity[]=$at;
        }

        if ($xAttr) $cond['obj_xAttributes']=sprintf('0x%x',$xAttr);
        if (count($xCity)) $cond['obj_xCityFts']=implode('|',$xCity);

        return $cond;
    }

    private function add_service($add_service)
    {
        $_add_service=$this->session('add_service');
        $result=array();

        if (strlen($add_service))
        {
            foreach (explode(',',$add_service) AS $as)
            {
                $asas=explode(':',$as);
                if (is_array($_add_service[$asas[0]]))
                {
                    $_asas=$_add_service[$asas[0]];
                    if (strlen($asas[1]))
                    {
                        $c=count($_asas['allocation']['data']);
                        for($i=0;$i<$c;$i++)
                        {
                            if (!$asas[1][$i]) unset($_asas['allocation']['data'][$i]);
                        }
                        sort($_asas['allocation']['data']);
                    }
                    $result[]=$_asas;
                }
            }
        }

        return $result;
    }

    public function extradata($token,$dor=0,$dzieci=0,$inf=0,$only_attr=false)
    {
        static $cache;
        
        if (!strlen($token)) return;
        
        $cond=array('ofr_id'=>$token);
        if ($dor) $cond['par_adt']=$dor;
        if ($dzieci) $cond['par_chd']=$dzieci;
        if ($inf) $cond['par_inf']=$inf;

        $cache_token = md5(serialize($cond));
        
        if (isset($cache[$cache_token])) return $cache[$cache_token];            
        
        $xml=$this->request('extradata',$cond);
        $resp = $this->post_xml($xml,'extradata');


        if (!isset($resp->base_data->extra_data) && !isset($resp->extra_data)) return false;

        $extra_data = isset($resp->extra_data) ? $resp->extra_data : $resp->base_data->extra_data;
        
        
        $attr=$this->getAttributes($extra_data);

                
        
        if($only_attr)
        {
            $wynik=$attr;
        }
        else
        {
            $wynik=array();
            foreach ($extra_data AS $htl)
            {
                if (!isset($htl->htlCode)) continue;
    
                $d=array('code'=>(string)$htl->htlCode);
    
                $d['id']=$this->getAttribute($htl,'extra_id');
                $d['value']=$d['id'].'_'.$d['code'];
    
                $d['name']=(string)$htl->htlName;
                $d['cat']=(string)$htl->htlCat;
                $d['service_code']=(string)$htl->htlSrvCode;
                $d['room_code']=(string)$htl->htlRoomCode;
                $d['price']=(float)$htl->prcAdt;
                $d['city']=(string)$htl->htlCity;
    
                $d['room']=(string)$htl->htlRoomDesc;
                $d['from']=(string)$htl->fromDate;
                $d['to']=(string)$htl->toDate;
                $d['service']=(string)$htl->htlSrvDesc;
                
                $d['attr'] = $attr;
    
                $wynik[]=$d;
            }
        }
    
        $cache[$cache_token]=$wynik; 
        return $wynik;
    }

    public function check($token,$dor,$dzieci,$inf,$htl='',$add_service='',$wishes=null,$birthdays=null)
    {
        static $cache;
        
        $cond=array('ofr_id'=>$token);
        if ($dor) $cond['par_adt']=$dor;
        if ($dzieci) $cond['par_chd']=$dzieci;
        if ($inf) $cond['par_inf']=$inf;
        if ($htl) $cond['x_htl']=$htl;
        

        $more=array();

        if (count($as=$this->add_service($add_service))) $more['forminfo']['add_service']['data']=$as;

        if (!is_null($wishes)) $more['forminfo']['wishes']=$wishes;

        if (is_array($birthdays))
        {
            $cnt=0;
            foreach($birthdays AS $birthday)
            {
                $person=array();
                $cnt++;
                $person['birthdate']=date('d.m.Y',strtotime($birthday));
                
                if ($cnt<=$dor) $person['gender']='H';
                else {
                    //$age=$this->operator->age(date('Y-m-d',strtotime($birthday)));
                    $person['gender']=($age>=2 ? 'K' : 'I');
                }
                

                $more['forminfo']['Person']['data'][]=$person;
            }
        }

        $cache_token = md5(serialize($cond).serialize($more));
        
        if (isset($cache[$cache_token])) return $cache[$cache_token];
        
        $xml=$this->request('check',$cond,$more);
        $resp = $this->post_xml($xml,'check');

        $wynik=array();
        $wynik['status']=$this->getAttribute($resp->offerstatus,'status');
        $wynik['option']=$this->getAttribute($resp->offerstatus,'optionpossible');
        $wynik['price']=$this->getAttribute($resp->pricetotal,'price');
        $wynik['prices']=array();
        
        $er=error_reporting();
        error_reporting(0);
        foreach($resp->forminfo->Person->data AS $person) $wynik['prices'][]=array('price'=>(integer)$person->price->value,'gender'=>(string)$person->gender->selected);
        error_reporting($er);
        
        $wynik['currency']=$this->getAttribute($resp->pricetotal,'curr');
        $wynik['info']=$resp->merlin_offer_info->info;

        $wynik['result_message_code']=$this->getAttribute($resp->result_message,'msgCode');

        $wynik['prepayment']=$resp->forminfo->prepayment->data;
        $wynik['reservepay']=$resp->forminfo->reservepay->data;


        $wynik['add']=array();



        if (is_object($resp->forminfo->add_service) && count($resp->forminfo->add_service)>0) foreach ($resp->forminfo->add_service->data AS $add)
        {
            $number=$add->number->value+0;
            $code=''.$add->code->value;

            $wynik['add'][$code]['number']=$number;
            foreach($add->allocation->data AS $allocation)
            {
                $wynik['add'][$code]['allocation']['data'][]=0+$allocation->value;
            }
            $wynik['add'][$code]['fromDT']=''.$add->fromDT->values->data[0];
            $wynik['add'][$code]['toDT']=''.$add->toDT->selected;

            //$wynik['add'][$code]['type']=''.$add->number->type;

            $wynik['add'][$code]['accomodation']='';
            $wynik['add'][$code]['shift']='';

            $wynik['add'][$code]['default_checked']= (0+$add->number->checked) ? 1:0;
            $wynik['add'][$code]['type']=''.$add->type->value;
            $wynik['add'][$code]['code']=''.$add->code->value;
            $wynik['add'][$code]['len']=0+$add->len->value;
            $wynik['add'][$code]['text']=''.$add->text->value;
            $wynik['add'][$code]['exclude']=''.$add->excludeIndex->value;

        }

        $this->session('add_service',$wynik['add']);

        
        $cache[$cache_token]=$wynik;
        return $wynik;
    }


    public function book($r,$option=true,$wishes=null,$htl='')
    {
        $cond=array('ofr_id'=>$r['r_token']);
        if ($htl) $cond['x_htl']=$htl;

        $forminfo['InternalAction']=0;
        $forminfo['DelPersonIdx']=0;
        $forminfo['ReservationMode']=$option?0:1;
        $forminfo['check_price']=0;
        $forminfo['flaga']=0;
        $forminfo['unload_flag']=1;
        $forminfo['short_term']=0;
        $forminfo['additional_where_flag']='';
        $forminfo['load_orderby']=0;
        $forminfo['hideDefBirthdates']=1;

        $forminfo['new_search']='';
        $forminfo['check_payment_offer']=0;
        $forminfo['email_condition_checked']=0;
        $forminfo['client_radio']='';
        $forminfo['family_address']='';
        $forminfo['test1_0']=0;
        $forminfo['test1_1']=0;
        $forminfo['test2_0']=0;
        $forminfo['test2_1']=0;
        $forminfo['test3_0']=0;
        $forminfo['test3_1']=0;
        $forminfo['test4_0']=0;
        $forminfo['test4_1']=0;
        $forminfo['test5_0']=0;
        $forminfo['test5_1']=0;
        $forminfo['conditions']='';

        if (!is_null($wishes)) $forminfo['wishes']=$wishes;

        if (strlen($r['r_zgody'])) foreach (explode(',',$r['r_zgody']) AS $agree) $forminfo[$agree]=1;

        $dor=0;
        $children=0;
        $infants=0;
        for ($i=0;$i<count($r['uczestnicy']);$i++)
        {
            $person=array();

            $person['lastname']=$r['uczestnicy'][$i]['ru_nazwisko'];
            $person['name']=$r['uczestnicy'][$i]['ru_imie'];

            $person['birthdate']=$r['uczestnicy'][$i]['ru_data_ur']?date('d.m.Y',strtotime($r['uczestnicy'][$i]['ru_data_ur'])):'01.01.1970';
            $person['price']=0;

    //do dyskusji
            $person['zipcode']=$r['klient']['k_kod'];
            $person['city']=$r['klient']['k_miasto'];
            $person['street']=$r['klient']['k_ulica'].' '.$r['klient']['k_nr_domu'];
            $person['phone']=$r['klient']['k_telefon'];;
    //</do dyskusji
            $person['email']='';

            $person['gender']=$r['uczestnicy'][$i]['ru_plec'];

            $forminfo['Person']['data'][]=$person;

            //$dorosly=$this->operator->age($r['uczestnicy'][$i]['ru_data_ur'],$r['r_data_przylotu_pow'])>=18;
            if (!$r['uczestnicy'][$i]['ru_data_ur']) $dorosly=true;

            if($dorosly)$dor++;
            else
            {
                /*
                if ($this->operator->age($r['uczestnicy'][$i]['ru_data_ur'],$r['r_data_przylotu_pow'])>=2)
                    $children++;
                else
                    $infants++;
                */

            }
        }

        $cond['par_adt']=$dor;

        if ($children) $cond['par_chd']=$children;
        if ($infants) $cond['par_inf']=$infants;

        $forminfo['Client']['lastname']=$r['klient']['k_nazwisko'];
        $forminfo['Client']['name']=$r['klient']['k_imie'];
        $forminfo['Client']['street']=$r['klient']['k_ulica'].' '.$r['klient']['k_nr_domu'];
        $forminfo['Client']['zipcode']=$r['klient']['k_kod'];
        $forminfo['Client']['city']=$r['klient']['k_miasto'];
        $forminfo['Client']['phone']=$r['klient']['k_telefon'];
        $forminfo['Client']['email']=$r['klient']['k_email'];


        $forminfo['Client']['country']='Polska';


        if (count($as=$this->add_service($r['r_ubezpieczenia']))) $forminfo['add_service']['data']=$as;


        $xml=$this->request('book',$cond,array('forminfo'=>$forminfo));
        $xml=$this->str_to_url($xml);
        $resp = $this->post_xml($xml,'book');

        $wynik=array();
        if (isset($resp->booking_number)) $wynik['booking_number']=$resp->booking_number;
        else
        {
            $wynik['error']=''.$resp->booking_info;
        }

        if (isset($resp->booking_errors->booking_error)) $wynik['error']=$resp->booking_errors->booking_error;

        if (is_array($wynik['error'])) $wynik['error']=implode("\n",$wynik['error']);
        $wynik['msg_type']=$this->getAttribute($resp->result_message,'msgType');
        $wynik['msg_code']=$this->getAttribute($resp->result_message,'msgCode');
        $wynik['price']=$this->getAttribute($resp->pricetotal,'price');
        $wynik['currency']=$this->getAttribute($resp->pricetotal,'curr');

        $wynik['info']=$resp->merlin_offer_info->info;

        return $wynik;
    }


    public function str_to_url($s, $case=0)
    {
        $acc =	'É	Ê	Ë	š	Ì	Í	ƒ	œ	µ	Î	Ï	ž	Ð	Ÿ	Ñ	Ò	Ó	Ô	Š	£	Õ	Ö	Œ	¥	Ø	Ž	§	À	Ù	Á	Ú	Â	Û	Ã	Ü	Ä	Ý	';
        $str =	'E	E	E	s	I	I	f	o	m	I	I	z	D	Y	N	O	O	O	S	L	O	O	O	Y	O	Z	S	A	U	A	U	A	U	A	U	A	Y	';

        $acc.=	'Å	Æ	ß	Ç	à	È	á	â	û	Ĕ	ĭ	ņ	ş	Ÿ	ã	ü	ĕ	Į	Ň	Š	Ź	ä	ý	Ė	į	ň	š	ź	å	þ	ė	İ	ŉ	Ţ	Ż	æ	ÿ	';
        $str.=	'A	A	S	C	a	E	a	a	u	E	i	n	s	Y	a	u	e	I	N	S	Z	a	y	E	i	n	s	z	a	p	e	I	n	T	Z	a	y	';

        $acc.=	'Ę	ı	Ŋ	ţ	ż	ç	Ā	ę	Ĳ	ŋ	Ť	Ž	è	ā	Ě	ĳ	Ō	ť	ž	é	Ă	ě	Ĵ	ō	Ŧ	ſ	ê	ă	Ĝ	ĵ	Ŏ	ŧ	ë	Ą	ĝ	Ķ	ŏ	';
        $str.=	'E	l	n	t	z	c	A	e	I	n	T	Z	e	a	E	i	O	t	z	e	A	e	J	o	T	i	e	a	G	j	O	t	e	A	g	K	o	';

        $acc.=	'Ũ	ì	ą	Ğ	ķ	Ő	ũ	í	Ć	ğ	ĸ	ő	Ū	î	ć	Ġ	Ĺ	Œ	ū	ï	Ĉ	ġ	ĺ	œ	Ŭ	ð	ĉ	Ģ	Ļ	Ŕ	ŭ	ñ	Ċ	ģ	ļ	ŕ	Ů	';
        $str.=	'U	i	a	G	k	O	u	i	C	g	k	o	U	i	c	G	L	O	u	i	C	g	l	o	U	o	c	G	L	R	u	n	C	g	l	r	U	';

        $acc.=	'ò	ċ	Ĥ	Ľ	Ŗ	ů	ó	Č	ĥ	ľ	ŗ	Ű	ô	č	Ħ	Ŀ	Ř	ű	õ	Ď	ħ	ŀ	ř	Ų	ö	ď	Ĩ	Ł	Ś	ų	Đ	ĩ	ł	ś	Ŵ	ø	đ	';
        $str.=	'o	c	H	L	R	u	o	C	h	l	r	U	o	c	H	L	R	u	o	D	h	l	r	U	o	d	I	L	S	c	D	i	l	s	W	o	d	';

        $acc.=	'Ī	Ń	Ŝ	ŵ	ù	Ē	ī	ń	ŝ	Ŷ	Ə	ú	ē	Ĭ	Ņ	Ş	ŷ';
        $str.=	'I	N	S	w	u	E	i	n	s	Y	e	u	e	I	N	S	y';

        $acc.=	'Б	б	В	в	Г	г	Д	д	Ё	ё	Ж	ж	З	з	И	и	Й	й	К	к	Л	л	М	м	Н	н	П	п	О	о	Р	р	С	с	Т	т	У	у	Ф	ф	Х	х	Ц	ц	Ч	ч	Ш	ш	Щ	щ	Ъ	Ы	ы	Ь	Э	э	Ю	ю	Я	я';
        $str.=	'B	b	W	w	G	g	D	d	Yo	yo	Z	z	Z	z	I	i	N	n	K	k	L	l	M	m	H	h	P	p	O	o	P	p	S	s	T	t	U	u	f	F	Ch	h	C	c	C	c	Sz	sz	S	s	-	Y	y	-	E	e	Iu	iu	Ia	ia';



        $out = str_replace(explode("\t", $acc), explode("\t", $str), $s);

        if($case == -1)
        {
            return strtolower($out);
        }
        else if($case == 1)
        {
            return strtoupper($out);
        }
        else
        {
            return ($out);
        }
    }

}
