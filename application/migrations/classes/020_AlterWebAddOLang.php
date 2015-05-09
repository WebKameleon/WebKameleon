<?php

class AlterWebAddOLang extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webtd';
    protected $_tableName2  = 'weblink';
    protected $_tableName3  = 'webpage';
    
    protected $_columnName1 = 'olang';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'character', 2, array('notnull' => false));
        $this->addColumn($this->_tableName2, $this->_columnName1, 'character', 2, array('notnull' => false));
        $this->addColumn($this->_tableName3, $this->_columnName1, 'character', 2, array('notnull' => false));
        
    }
    
    public function postUp()
    {
        Doctrine_Manager::connection()->exec("UPDATE ".$this->_tableName1." SET ".$this->_columnName1."=lang");
        Doctrine_Manager::connection()->exec("UPDATE ".$this->_tableName2." SET ".$this->_columnName1."=lang");
        Doctrine_Manager::connection()->exec("UPDATE ".$this->_tableName3." SET ".$this->_columnName1."=lang");
    }

    public function down()
    {
        $this->removeColumn($this->_tableName3, $this->_columnName1);
        $this->removeColumn($this->_tableName2, $this->_columnName1);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
