<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class gcalWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'gcal';

    /**
     * @var string
     */
    public $calendar_url;

    /**
     * @var array
     */
    public $calendars_list;

    public function init()
    {
        parent::init();

        $this->calendar_url = $this->getCalendarUrl();
    }

    public function edit()
    {
        $this->check_scope('calendar',$_GET['page']);   
        $this->calendars_list = $this->getCalendarsList();
        
    }

    /**
     * @param array $options
     * @return string
     */
    public function getCalendarUrl()
    {
        $data = $this->data;
        
        $URL = 'https://www.google.com/calendar/embed?';
        if (isset($data['calendar'])) {
            foreach ($data['calendar'] as $gcal) {
                $URL .= 'src=' . $gcal . '&';
            }
            unset($data['calendar']);
        }
        foreach ($data as $k => $v) {
            if ($v === '') unset($data[$k]);
            if ($k=='date') {
                unset($data[$k]);
                if($v) $data['dates']=str_replace('-','',$v).'/'.str_replace('-','',$v);
            }
        }
        $URL .= http_build_query($data);
        return $URL;
    }

    /**
     * @return array
     */
    public function getCalendarsList()
    {
        $calendarsList = $this->getCalendarService()->calendarList->listCalendarList();
        
        $items=$calendarsList->getItems();
        
        $ret=array();
        
        foreach($items AS $i=>$v)
        {
            if (true || $v->accessRole!='reader')
            {
                $ret[]=(array)$v;
    
            }

            
        }
        
        return $ret;
    }

    /**
     * @return Google_CalendarService
     */
    public function getCalendarService()
    {
        static $service;
        if ($service == null) {
            $service = Google::getCalendarService();
        }
        return $service;
    }
}
