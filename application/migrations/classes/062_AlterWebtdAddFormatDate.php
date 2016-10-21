<?php

class AlterWebtdAddFormatDate extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webtd';
    protected $_columnName1 = 'date_format';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'text', null, array(
            'notnull' => false
        ));

    }


    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
