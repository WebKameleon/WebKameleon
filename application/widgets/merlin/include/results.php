<?php
    
    include __DIR__.'/pre.php';  
    $merlin_resultsWidget['q']=$merlin_q;


    if (!isset($type)) $type='';
    $order='';
    $limit=$size?:10;
    $offset=0;  
  
    $cond=[];
    
    if (isset($merlin_q['dest'])) $cond['dest']=$merlin_q['dest'];
    if (isset($merlin_q['dep'])) $cond['dep']=$merlin_q['dep'];
    if (isset($merlin_q['to'])) $cond['to']=$merlin_q['to'];
    if (isset($merlin_q['from'])) $cond['from']=$merlin_q['from'];
    if (isset($merlin_q['fromto'])) $cond['fromto']=$merlin_q['fromto'];
    if (isset($merlin_q['hotel'])) $cond['hotel']=$merlin_q['hotel'];
    if (isset($merlin_q['xhotel'])) $cond['xhotel']=$merlin_q['xhotel'];

    if (isset($merlin_q['adt'])) $cond['adt']=$merlin_q['adt'];
    if (isset($merlin_q['chd'])) $cond['chd']=$merlin_q['chd'];
    
    
    if (isset($merlin_q['page'])) $offset=($merlin_q['page']-1)*$limit;
    else $merlin_q['page']=1;
    
    switch ($type)
    {
        case 1:
            $offers=$merlin->getOffers($cond,$type,$order,$limit,$offset);
            break;
        
        default:
            $offers=$merlin->getGrouped($cond,$type,$order,$limit,$offset);
            break;
    }
    
  
    
    $merlin_q_nopage=$merlin_q;
    if (isset($merlin_q_nopage['page'])) unset($merlin_q_nopage['page']);
  
    $merlin_resultsWidget['offers']=$offers;
    $merlin_resultsWidget['merlin_url_vars']=merlin_q2url($merlin_q_nopage);   
    
    if (isset($offers['count']) && $offers['count']>$limit)
    {
        $nav=array();
        for ($i=1; $i<=ceil($offers['count']/$limit);$i++)
        {
            $nav[]=array('page'=>$i,'active'=>$merlin_q['page']==$i,'vars'=>merlin_q2url(array_merge($merlin_q,array('page'=>$i))));
        }
        
        $merlin_resultsWidget['nav']=$nav;
    }

    $merlin_resultsWidget['dbg']=$merlin->debug;
