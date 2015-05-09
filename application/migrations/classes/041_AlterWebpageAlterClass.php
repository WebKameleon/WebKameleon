<?php

class AlterWebpageAlterClass extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webpage';
    protected $_columnName1 = 'class';


    public function up()
    {
        $this->changeColumn($this->_tableName1, $this->_columnName1,'Varchar',50);

    }

    public function down()
    {
        $this->changeColumn($this->_tableName1, $this->_columnName1,'Char',12);
    }
}
