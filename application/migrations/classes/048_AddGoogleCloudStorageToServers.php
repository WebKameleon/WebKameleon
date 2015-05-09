<?php

class AddGoogleCloudStorageToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'gcs_bucket';
    protected $_columnName2 = 'gcs_website';
    
    protected $_tableName2  = 'passwd';
    protected $_columnName3 = 'from_campaign';
    


    public function up()
    {
        $this->addColumn($this->_tableName1, $this->_columnName1, 'varchar(128)', null, array('notnull' => false));
        $this->addColumn($this->_tableName1, $this->_columnName2, 'varchar(255)', null, array('notnull' => false));
        $this->addColumn($this->_tableName2, $this->_columnName3, 'varchar(128)', null, array('notnull' => false));
        
    }

    public function down()
    {
        $this->removeColumn($this->_tableName2, $this->_columnName3);
        $this->removeColumn($this->_tableName1, $this->_columnName2);
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }

}
