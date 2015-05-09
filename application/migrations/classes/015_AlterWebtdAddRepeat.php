<?php

class AlterWebtdAddRepeat extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webtd';
    protected $_columnName1 = 'repeat';


    protected function _columnName()
    {
        if (strtolower(Doctrine_Manager::connection()->getDriverName())!='pgsql') $this->_columnName1='contents_repeat';
    }

    public function up()
    {
        $this->_columnName();
        
        $int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';
        
        $this->addColumn($this->_tableName1, $this->_columnName1, $int2.' DEFAULT 0', null, array(
            'notnull' => false
        ));

    }

    public function down()
    {
        $this->_columnName();
        
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
