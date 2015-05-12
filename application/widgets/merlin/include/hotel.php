<?php

    include __DIR__.'/pre.php';  
    $merlin_hotelWidget['q']=$merlin_q;


    if (isset($merlin_q['id']) && $merlin_q['id'] )
    {

        $offer=$merlin->getOfferOnToken($merlin_q['id']);
        if (isset($offer['obj']))
        {
            if (isset($offer['obj']['info']['desc']) && is_array($offer['obj']['info']['desc'])) foreach ($offer['obj']['info']['desc'] AS $desc)
            {
                $offer['obj']['hotel'][$desc['subject']] = $desc['content'];
            }
            //$merlin_hotelWidget['hotel']=$offer['obj'];
            $merlin_hotelWidget['hotel']=$offer;
            
        }
        
    }
    
    $merlin_hotelWidget['dbg']=$merlin->debug;
  
    

