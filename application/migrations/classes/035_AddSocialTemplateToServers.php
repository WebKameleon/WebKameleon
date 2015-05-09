<?php

class AddSocialTemplateToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'social_template';
    protected $_columnName2 = 'from_social_template';


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'varchar(32)', null, array('notnull' => false ));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'varchar(32)', null, array('notnull' => false));
        
        $this->addIndex( $this->_tableName1, $this->_tableName1.'_'.$this->_columnName1.'_key', array('fields'=>array($this->_columnName1)) );
        $this->addIndex( $this->_tableName1, $this->_tableName1.'_'.$this->_columnName2.'_key', array('fields'=>array($this->_columnName2)) );

    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }
}
