<?php

class ObserverInsert extends Doctrine_Migration_Base
{

    public function up()
    {
        $sql=file_get_contents(preg_replace('/\.php$/','_up.sql',__FILE__));
        Doctrine_Manager::connection()->exec($sql);
        
    }
    public function down()
    {
 
        $sql=file_get_contents(preg_replace('/\.php$/','_down.sql',__FILE__));
        Doctrine_Manager::connection()->exec($sql);
    }
}



