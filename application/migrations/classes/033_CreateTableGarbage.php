<?php

class CreateTableGarbage extends Doctrine_Migration_Base
{
    private $_tableName = 'ftpgarbage';

    
    public function up() {
        
        $int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';
        
      $this->createTable($this->_tableName,array(
             'id'=>array('type'=>'integer','notnull' => true,'autoincrement' => true,'primary'=>true),            
             'server'=>array('type'=>'varchar(128)'),
             'username'=>array('type'=>'text'),
             'pass'=>array('type'=>'varchar(64)'),
             'passive'=>array('type'=>$int2),
             'dir'=>array('type'=>'text'),
             'nd_create'=>array('type'=>'integer'),
             'nd_complete'=>array('type'=>'integer default 0'),
        ));
      
        $this->addIndex( $this->_tableName, $this->_tableName.'_date_key', array('fields'=>array('nd_complete')) );
    }
    
    public function down() {
        
        $this->dropTable($this->_tableName);
    }
    
 
}
