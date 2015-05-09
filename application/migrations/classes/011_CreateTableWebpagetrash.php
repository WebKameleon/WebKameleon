<?php

class CreateTableWebpagetrash extends Doctrine_Migration_Base
{
    private $_tableName = 'webpagetrash';

    
    public function up() {
        $this->conn=Doctrine_Manager::connection();
        
        $old=false;
        $webtd=$this->conn->commit();
        $this->conn->beginTransaction();
        
        try {
            $webtd=$this->conn->fetchOne('SELECT count(*) FROM kameleon');
            $old=true;
        }
        catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->beginTransaction();
            $this->newKameleonUp();
        }
        if ($old) $this->oldKameleonUp();
    }
    
    
    
    public function down() {
        $this->conn=Doctrine_Manager::connection();

        $old=false;
        $webtd=$this->conn->commit();
        $this->conn->beginTransaction();
                
        try {
            $webtd=$this->conn->fetchOne('SELECT count(*) FROM kameleon');
            $old=true;
        }
        catch (Exception $e) {
            $this->conn->rollback();
            $this->conn->beginTransaction();            
            $this->newKameleonDown();
        }        
        if ($old) $this->oldKameleonDown();
    }
    
    
    protected function newKameleonUp() {
      $this->createTable($this->_tableName,array(
             'id'=>array('type'=>'integer','notnull' => true,'autoincrement' => true, 'primary'=>true),            
             'page_id'=>array('type'=>'integer'),
             'ver'=>array('type'=>'integer'),
             'lang'=>array('type'=>'character(2)'),
             'server'=>array('type'=>'integer'),
             'nd_issue'=>array('type'=>'integer'),
             'nd_complete'=>array('type'=>'integer'),
             'status'=>array('type'=>'character(1)'),
             'file_name'=>array('type'=>'text'),
             
        ));
      
        $this->addIndex( $this->_tableName, $this->_tableName.'_all_key', array('fields'=>array('server','lang','ver','page_id','status')) );
        $this->addIndex( $this->_tableName, $this->_tableName.'_date_key', array('fields'=>array('nd_complete','status')) );
    }
    
    protected function oldKameleonUp() {
    }
    
    protected function oldKameleonDown() {
    }
    
    protected function newKameleonDown() {
        
        $this->dropTable($this->_tableName);
    }
    
 
}
