<?php

class AddAppEngineToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'appengine_id';
    protected $_columnName2 = 'appengine_ver';
    protected $_columnName3 = 'appengine_scripts';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'varchar(128)', null, array('notnull' => false ));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'integer', null, array('notnull' => false, 'default'=>1, 'value'=>1));
        $this->addColumn($this->_tableName1, $this->_columnName3, 'text', null, array('notnull' => false ));
        
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName3);
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
