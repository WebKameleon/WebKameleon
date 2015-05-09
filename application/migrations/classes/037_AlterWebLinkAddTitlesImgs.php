<?php

class AlterWebLinkAddTitlesImgs extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'weblink';
    
    protected $_columnName1 = 'titlea';
    protected $_columnName2 = 'titleb';
    protected $_columnName3 = 'titlec';
    protected $_columnName4 = 'imgb';
    protected $_columnName5 = 'imgc';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'text', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'text', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName3, 'text', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName4, 'text', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName5, 'text', null, array('notnull' => false));
        
    }
    

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName5);
        $this->removeColumn($this->_tableName1, $this->_columnName4);
        $this->removeColumn($this->_tableName1, $this->_columnName3);
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
