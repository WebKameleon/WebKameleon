<?php
/**
 * @author <radoslaw.szczepaniak@gammanet.pl> Radosław Szczepaniak
 */

class CreateTablePayments extends Doctrine_Migration_Base
{
    private $_tableName = 'payments';

    public function up()
    {
        
        $int2=Doctrine_Manager::connection()->getDbh()->getAttribute(PDO::ATTR_DRIVER_NAME)=='pgsql'?'int2':'integer(2)';
        $this->createTable($this->_tableName, array(
            'id' => array(
                'type' => 'integer',
                'notnull' => true,
                'primary' => true,
                'autoincrement' => true,
            ),
            'name' => array(
                'type' => 'varchar(256)',
                'notnull' => true,
            ),
            'type' => array(
                'type' => $int2,
                'notnull' => true,
            ),
            'date' => array(
                'type' => 'timestamp',
                'notnull' => true,
            ),
            'transaction_id' => array(
                'type' => 'varchar(256)',
                'notnull' => false,
            ),
            'amount' => array(
                'type' => 'decimal(12,2)',
                'notnull' => false,
            ),
            'status' => array(
                'type' => 'varchar(256)',
                'notnull' => false,
            ),
            'payer_email' => array(
                'type' => 'varchar(256)',
                'notnull' => false,
            ),
            'custom_id' => array(
                'type' => 'varchar(32)',
                'notnull' => false
            ),
            'custom_data' => array(
                'type' => 'text',
                'notnull' => false
            ),
            'response_data' => array(
                'type' => 'text',
                'notnull' => false
            ),
        ));
    }

    public function postUp()
    {
        
        if (strtolower(Doctrine_Manager::connection()->getDriverName())=='mysql')
            $sql='ALTER TABLE ' . $this->_tableName . ' CHANGE `date` `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP';
        else
            $sql='ALTER TABLE ' . $this->_tableName . ' ALTER COLUMN date SET DEFAULT CURRENT_TIMESTAMP';
        
        Doctrine_Manager::connection()->exec($sql);
    }

    public function down()
    {
        $this->dropTable($this->_tableName);
    }
}