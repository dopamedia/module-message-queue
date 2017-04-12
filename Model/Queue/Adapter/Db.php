<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Model\Queue\Adapter;

class Db extends \Zend_Queue_Adapter_Db
{
    /**
     * @param array|\Zend_Config $options
     * @param \Zend_Queue|null $queue
     * @throws \Zend_Queue_Exception
     */
    public function __construct($options, \Zend_Queue $queue = null)
    {
        \Zend_Queue_Adapter_AdapterAbstract::__construct($options, $queue);

        if (!isset($this->_options['options'][\Zend_Db_Select::FOR_UPDATE])) {
            // turn off auto update by default
            $this->_options['options'][\Zend_Db_Select::FOR_UPDATE] = false;
        }

        if (!is_bool($this->_options['options'][\Zend_Db_Select::FOR_UPDATE])) {
            #require_once 'Zend/Queue/Exception.php';
            throw new \Zend_Queue_Exception('Options array item: Zend_Db_Select::FOR_UPDATE must be boolean');
        }

        if (!isset($this->_options['dbAdapter']) ||
            !$this->_options['dbAdapter'] instanceof \Zend_Db_Adapter_Abstract) {
            throw new \Zend_Queue_Exception('dbAdapter must be set');
        }

        if (!isset($this->_options['dbQueueTable']) || empty($this->_options['dbQueueTable'])) {
            throw new \Zend_Queue_Exception('dbQueueTable must be set');
        }

        if (!isset($this->_options['dbMessageTable']) || empty($this->_options['dbMessageTable'])) {
            throw new \Zend_Queue_Exception('dbMessageTable must be set');
        }

        $this->_queueTable = new \Zend_Db_Table(
            [
                'db' => $this->_options['dbAdapter'],
                'name' => $this->_options['dbQueueTable']
            ]
        );

        $this->_messageTable = new \Zend_Db_Table(
            [
                'db' => $this->_options['dbAdapter'],
                'name' => $this->_options['dbMessageTable']
            ]
        );
    }
}