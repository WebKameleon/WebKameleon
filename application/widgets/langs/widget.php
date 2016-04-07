<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class langsWidget extends Widget
{
    public $name = 'langs';

    /**
     * @var array
     */
    public $all_languages,$cp_languages,$page_target;

    public function init()
    {
        parent::init();

        $langs_used = Bootstrap::$main->session('langs_used');
        
        $this->all_languages=array();
        $this->cp_languages=array();

        
        foreach ($langs_used as $lang) {
            $tmp = array();
            $tmp['code'] = $lang;
            $tmp['name'] = $name = Tools::translate($lang);
            $tmp['checked'] = !isset($this->data['languages']) || array_key_exists($lang, $this->data['languages']);

            $this->all_languages[]=$tmp;
        }

        foreach ($langs_used as $lang) {
            $tmp = array();
            if ($lang==$this->webtd['lang']) continue;
            
            $tmp['code'] = $lang;
            $tmp['name'] = $name = Tools::translate($lang);
            $tmp['checked'] = isset($this->data['cp_languages']) && array_key_exists($lang, $this->data['cp_languages']);

            $this->cp_languages[]=$tmp;
        }

    }

    public function run()
    {
        parent::run();

        
        $langs = isset($this->data['languages']) ? array_keys($this->data['languages']) : Bootstrap::$main->session('langs_used');

        $this->all_languages=array();
        
        foreach ($langs AS $lang) $this->all_languages[$lang]=Tools::translate($lang);
        
        $this->page_target = isset($this->data['home']) && $this->data['home'] ? '0' :  $this->webpage['id'];
        
        if (isset($this->data['cp_languages']) && is_array($this->data['cp_languages']) && count($this->data['cp_languages']) && Bootstrap::$main->session('editmode')>1) $this->cpl(array_keys($this->data['cp_languages']));
    }
    
    
    protected function cpl($langs)
    {
        $webpage=new webpageModel($this->webpage['sid']);
        $webpage2=new webpageModel();
        $langs_related=unserialize($webpage->langs_related);
        
        if (!is_array($langs_related)) $langs_related=array();

        $webtd=new webtdModel();
        $tdks=$webtd->getAll(array($webpage->id));
        
        foreach ($langs AS $lang) {
            if (!strlen($lang) || $lang==$this->webpage['lang']) continue;
            
            $webpage2->lang=$lang;
            $p=$webpage2->getOne($this->webpage['id'],true);
            
            if (!$p) {
                $data=$this->webpage;
                $data['lang']=$lang;
                foreach(array('sid','nd_create','nd_update','nd_ftp','langs_related') AS $k)
                    if (in_array($k,array_keys($data)))
                        unset($data[$k]);
                $data['hidden']=1;
                $wp=new webpageModel($data);
                $p=$wp->save();
                
            }
            
            if (!isset($langs_related[$lang]) || !is_array($langs_related[$lang])) {
                $tdks_related=$webtd->getAll(array($webpage->id),0,$lang);
                $langs_related[$lang]=array();
                
                    
                foreach ($tdks AS $i=>$td)
                {
                    $tdsid=$td['sid'];
                    if (isset($tdks_related[$i]))
                        $langs_related[$lang][$tdsid]=array($tdks_related[$i]['sid']);
                    
                }
                
            } else {
                foreach ($tdks AS $td) {
                    $tdsid=$td['sid'];
                   
                    $related_sid=isset($langs_related[$lang][$tdsid])?end($langs_related[$lang][$tdsid]):0;
                    
                    $related_td=array('nd_update'=>0,'hidden'=>0);
                    if ($related_sid) {
                        $related_td=$webtd->get($related_sid);
                        if ($related_td['trash']) $related_td=array('nd_update'=>0,'hidden'=>0);
                    }
                    

                    
                    if ($td['nd_update'] > $related_td['nd_update']) {
                        
                        if ($related_td['hidden']) {
                            
                            $webtd2=new webtdModel($related_td,false);
                            $webtd2->plain = $td['plain'];
                            $webtd2->title = $td['title'];
                            $webtd2->trailer = $td['trailer'];
                            $webtd2->nd_update = Bootstrap::$main->now;
                            $webtd2->save();
                            
                            
                        } else {
                            foreach(array('sid','uniqueid') AS $k)
                                if (in_array($k,array_keys($td)))
                                    unset($td[$k]);
                                    
                            $webtd2=new webtdModel($td,true);
                            $webtd2->nd_create = Bootstrap::$main->now;
                            $webtd2->nd_update = Bootstrap::$main->now;
                            $webtd2->lang=$lang;
                            $webtd2->hidden=1;
                            $webtd2->pri=$webtd2->next_pri();
                            
                            $newtd=$webtd2->save();
                            if (isset($newtd['sid']) && $newtd['sid']) {
                                if (!isset($langs_related[$lang][$tdsid]))
                                    $langs_related[$lang][$tdsid]=array();
                                
                                $langs_related[$lang][$tdsid][]=$newtd['sid'];
                                
                            }
                            
                              
                        }
                        
                        
                    }
                    
                }
            }
            
        }
                

        $webpage->langs_related=serialize($langs_related);
        $webpage->save();
    }
}