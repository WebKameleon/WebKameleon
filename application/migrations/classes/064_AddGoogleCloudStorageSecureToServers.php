<?php

class AddGoogleCloudStorageSecureToServers extends Doctrine_Migration_Base
{
    protected $_tableName1  = 'servers';
    protected $_columnName1 = 'gcs_secure';
    
    


    public function up()
    {
	$int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';
        $this->addColumn($this->_tableName1, $this->_columnName1,$int2, null, array('notnull' => false));
        
    }

    public function down()
    {
        $this->removeColumn($this->_tableName1, $this->_columnName1);
    }

}
