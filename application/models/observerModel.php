<?php

class observerModel extends Model
{
    /**
     * @return array
     */
    public function getAll($lang = null)
    {
        if ($lang == null)
            $lang = Bootstrap::$main->session('lang');

        return $this->conn->fetchAll('SELECT * FROM observer WHERE lang = ?', array(
            $lang
        ));
    }

    /**
     * @param null $lang
     * @return array
     */
    public function getList($lang = null)
    {
        return $this->conn->fetchAll('SELECT DISTINCT event, (SELECT COUNT(*) FROM observer o WHERE o.event = o2.event AND o.pri<>0) AS amount FROM observer o2 WHERE pri=0 ORDER BY event');
    }

    /**
     * @param string $event
     * @param string $lang
     * @return array
     */
    public function getEvents($event, $lang = null)
    {
        return $this->conn->fetchAll('SELECT * FROM observer WHERE event = ? AND pri<>0 ORDER BY pri,lang', array($event));
    }

    public function getObservers($event, $lang = null, $result = null, $days = null)
    {
        
        if ($lang == null)
            $lang = Bootstrap::$main->session('lang');

        $sql = "SELECT * FROM observer WHERE active = 1 AND pri >= ? AND event = '$event' AND lang = ?";

        if (!is_null($result))
            $sql .= ' AND (result IS NULL OR result = ' . $result . ')';

        if (!is_null($days))
            $sql .= ' AND (days IS NULL OR days = ' . $days . ')';
            
        $sql.=" ORDER BY pri";

        $data = $this->conn->fetchAll($sql, array(1,$lang));

        if (empty($data))
            $data = $this->conn->fetchAll($sql, array(1,'en'));

        if (empty($data))
            $data = $this->conn->fetchAll($sql, array(0,'en'));

            
        if (empty($data)) {
            
            $tmp = new self;

            $tmp->pri    = 0;
            $tmp->event  = $event;
            $tmp->lang   = 'en';
            $tmp->result = $result;
            $tmp->days   = $days;

            $tmp->save();

            $data = $this->conn->fetchAll($sql, array(1,$lang));
        }

        return $data;
    }
}
