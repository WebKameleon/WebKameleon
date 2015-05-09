<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class AlterServersAddLastPayment extends Doctrine_Migration_Base
{
    protected $_tableName  = 'servers';
    protected $_columnName = 'nd_last_payment';

    public function up()
    {
        $this->addColumn($this->_tableName, $this->_columnName, 'integer');
    }

    public function down()
    {
        $this->removeColumn($this->_tableName, $this->_columnName);
    }
}