<?php

class AddLoginTimeToLoginArch extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'login_arch';
    protected $_columnName1 = 'login_time';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'integer', null, array('notnull' => false ));

    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
