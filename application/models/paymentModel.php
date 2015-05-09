<?php
/**
 * @author Rados�aw Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class paymentModel extends Model
{
    protected $_table = 'payments';
    protected $_key = 'id';

    const TYPE_PAYPAL = 1;
    const TYPE_PAYU   = 2;

    /**
     * @param int $type
     * @param array $customData
     * @return paymentModel
     */
    public static function newPayment($type, array $customData)
    {
        $payment = new self;
        $payment->type = $type;
        $payment->name = $customData['server']['nazwa_long'];
        $payment->amount = isset($customData['info']['total']) ? $customData['info']['total'] : $customData['info']['amount'];
        $payment->setCustomData($customData);
        $payment->custom_id = md5($payment->custom_data . microtime(true) . mt_rand());
        $payment->save();

        return $payment;
    }

    /**
     * @return mixed
     */
    public function getCustomData()
    {
        return unserialize($this->custom_data);
    }

    /**
     * @param mixed $customData
     */
    public function setCustomData($customData)
    {
        $this->custom_data = serialize($customData);
    }

    /**
     * @param mixed $responseData
     */
    public function setResponseData($responseData)
    {
        $this->response_data = serialize($responseData);
    }

    /**
     * @return mixed
     */
    public function getResponseData()
    {
        return unserialize($this->response_data);
    }

    /**
     * @param string $custom_id
     * @return array
     */
    public function findCustom($custom_id)
    {
        $this->clear();
        $data = $this->conn->fetchRow('SELECT * FROM ' . $this->_table . ' WHERE custom_id = ?', array($custom_id));
        if ($data)
            $this->load($data);

        return $data;
    }

    /**
     * @param string $transaction_id
     * @param string $status
     * @return array
     */
    public function findTransaction($transaction_id, $status = null)
    {
        $this->clear();

        if ($status)
            $data = $this->conn->fetchRow('SELECT * FROM ' . $this->_table . ' WHERE transaction_id = ? AND status = ?', array($transaction_id, $status));
        else
            $data = $this->conn->fetchRow('SELECT * FROM ' . $this->_table . ' WHERE transaction_id = ?', array($transaction_id));

        if ($data)
            $this->load($data);

        return $data;
    }

    /**
     * @return array
     */
    public function data()
    {
        $data = parent::data();
        $data['custom_data'] = $this->getCustomData();
        $data['response_data'] = $this->getResponseData();

        return $data;
    }
}