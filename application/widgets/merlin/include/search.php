<?php
    include __DIR__.'/pre.php';
    
    if (!isset($merlin_q['adt'])) $merlin_q['adt']=2;
    
    $merlin_searchWidget['q']=$merlin_q;
    
    $regions=$merlin->getRegions()?:[];
    
    $countries=[];
    
    foreach ($regions AS $r)
    {
        $code=$r['country'].$r['country_code'];
        if (!isset($countries[$code]))
        {
            $countries[$code]=array('name'=>$r['country'],'codes'=>[],'regions'=>[]);
        
        }

        $countries[$code]['codes'][]=$r['id'];
        $countries[$code]['regions'][]=array('code'=>$r['id'],'name'=>$r['region'],'selected'=>$merlin_q['dest']==$r['id']);
        
    }
    ksort($countries);
    foreach ($countries AS &$country)
    {
        $country['selected']=$merlin_q['dest']==implode(',',$country['codes']);
    }
    
    $merlin_searchWidget['countries']=$countries;
    
    $departures=$merlin->getFilters([],'trp_depName');
    $merlin_searchWidget['departures']=$departures;
    
    $types=$merlin->getFilters([],'ofr_type');
    
    $merlin_searchWidget['types']=$types;
    
    
    $hotels=$merlin->getFilters([],'obj_xCode');
    asort($hotels);
    $merlin_searchWidget['hotels']=$hotels;
    
    $merlin_searchWidget['dbg']=$merlin->debug;
    
    

