<?php

class AddAnonumousToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'anonymous';
    protected $_columnName2 = 'anonymous_expire';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'varchar(32)', null, array('notnull' => false ));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'integer', null, array('notnull' => false, 'default'=>0, 'value'=>0));
        
        $this->addIndex( $this->_tableName1, $this->_tableName1.'_'.$this->_columnName1.'_key', array('fields'=>array($this->_columnName1)) );
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
