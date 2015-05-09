<?php

class PaymentsIndexes extends Doctrine_Migration_Base
{

    

    public function up()
    {


        $this->addIndex( 'payments', 'payments_custom_id_key_idx', array('fields'=>array('custom_id'),'type'=>'unique') );
        $this->addIndex( 'payments', 'payments_transaction_id_key_idx', array('fields'=>array('transaction_id')) );
    }

    public function down()
    {
        $this->removeIndex('payments','payments_custom_id_key');
        $this->removeIndex('payments','payments_transaction_id_key');
        
        
    }
}
