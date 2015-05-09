<?php

class AlterWebpageAddAppEngine extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webpage';
    protected $_columnName1 = 'appengine_login';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'text', null, array(
            'notnull' => false
        ));      
        
        
    }
    
    public function postUp()
    {
        if (strtolower(Doctrine_Manager::connection()->getDriverName())=="mysql")
            $sql="ALTER TABLE " . $this->_tableName1 . " CHANGE `appengine_login` `appengine_login` TEXT DEFAULT 'inherit'";
        else
            $sql="ALTER TABLE " . $this->_tableName1 . " ALTER COLUMN appengine_login SET DEFAULT 'inherit'";        
    
        Doctrine_Manager::connection()->exec($sql);
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
