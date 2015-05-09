<?php

class AlterPasswdAddKnowsHowToEditHF extends Doctrine_Migration_Base
{
    protected $_tableName  = 'passwd';
    protected $_columnName = 'knows_hf';

    public function up()
    {
        $int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';
        $this->addColumn($this->_tableName, $this->_columnName, $int2.' DEFAULT 0');
    }

    public function down()
    {
        $this->removeColumn($this->_tableName, $this->_columnName);
    }
}
