<?php

class AlterWebAddTrash extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'webpage';
    protected $_tableName2  = 'webtd';
    protected $_tableName3  = 'weblink';
    protected $_columnName1 = 'trash';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'integer', null, array(
            'notnull' => false, 'default'=>0, 'value'=>0
        ));
        $this->addColumn($this->_tableName2, $this->_columnName1, 'integer', null, array(
            'notnull' => false, 'default'=>0, 'value'=>0
        ));
        $this->addColumn($this->_tableName3, $this->_columnName1, 'integer', null, array(
            'notnull' => false, 'default'=>0, 'value'=>0
        ));

    }

    public function down()
    {
        $this->removeColumn($this->_tableName3, $this->_columnName1);
        $this->removeColumn($this->_tableName2, $this->_columnName1);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
