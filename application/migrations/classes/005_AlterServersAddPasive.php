<?php

class AlterServersAddPasive extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'ftp_pasive';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'int', null, array(
            'notnull' => false, 'default' => 1, 'value' => 1
        ));

    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
