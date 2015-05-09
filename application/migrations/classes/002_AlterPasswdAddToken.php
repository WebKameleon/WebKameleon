<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class AlterPasswdAddToken extends Doctrine_Migration_Base
{
    protected $_tableName  = 'passwd';
    protected $_columnName = 'access_token';

    public function up()
    {
        $this->addColumn($this->_tableName, $this->_columnName, 'text', null, array(
            'notnull' => false
        ));
    }

    public function down()
    {
        $this->removeColumn($this->_tableName, $this->_columnName);
    }
}