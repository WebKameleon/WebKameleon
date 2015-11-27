<?php

class AlterServersAddTrustAndReSitemap extends Doctrine_Migration_Base
{
    protected $_tableName  = 'servers';
    protected $_columnName1 = 'trust';
    protected $_columnName2 = 'resitemap';


    public function up()
    {
        $int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';
        
        $this->addColumn($this->_tableName, $this->_columnName1, $int2, null, array(
            'notnull' => false
        ));
        $this->addColumn($this->_tableName, $this->_columnName2, $int2, null, array(
            'notnull' => false
        ));
    }


    public function postUp()
    {
        Doctrine_Manager::connection()->execute('UPDATE '.$this->_tableName.' SET '. $this->_columnName2.'=1');
	
    }

    public function down()
    {
        $this->removeColumn($this->_tableName, $this->_columnName2);
        $this->removeColumn($this->_tableName, $this->_columnName1);
    }
  
}
