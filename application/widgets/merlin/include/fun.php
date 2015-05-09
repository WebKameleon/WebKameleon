<?php

    function merlin_q2url($q)
    {
        $res='';
        foreach ($q AS $k=>$v)
        {
            if ($res) $res.='&';
            $res.='merlin.'.$k.'='.urlencode($v);
        }
        return $res;
    }