<?php

class AlterWeblinkNameText extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'weblink';



    public function up()
    {
        if (strtolower(Doctrine_Manager::connection()->getDriverName())=="mysql")
            $sql="ALTER TABLE " . $this->_tableName1 . " MODIFY `name` TEXT ";
        else
            $sql="ALTER TABLE " . $this->_tableName1 . " ALTER COLUMN name TYPE Text";        

        Doctrine_Manager::connection()->exec($sql);
    }
    


    public function down()
    {
        if (strtolower(Doctrine_Manager::connection()->getDriverName())=="mysql")
            $sql="ALTER TABLE " . $this->_tableName1 . " MODIFY `name` Varchar(32) ";
        else
            $sql="ALTER TABLE " . $this->_tableName1 . " ALTER COLUMN name TYPE Varchar(32)";
    
        Doctrine_Manager::connection()->exec($sql);
    }
  
}
