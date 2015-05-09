<?php

class AlterPasswdAndNospam extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'passwd';
    protected $_columnName1 = 'nospam';



    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'Integer', null, array('notnull' => false));

    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
