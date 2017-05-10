<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Model\Queue\Adapter;

class Db extends \Zend_Queue_Adapter_Db
{

    /**
     * @var \Dopamedia\MessageQueue\Model\IteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var \Dopamedia\MessageQueue\Model\MessageFactory
     */
    private $messageFactory;

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

    /**
     * @param \Dopamedia\MessageQueue\Model\IteratorFactory $iteratorFactory
     * @return void
     */
    public function setIteratorFactory(\Dopamedia\MessageQueue\Model\IteratorFactory $iteratorFactory)
    {
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * @param \Dopamedia\MessageQueue\Model\MessageFactory $messageFactory
     */
    public function setMessageFactory(\Dopamedia\MessageQueue\Model\MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * @inheritdoc
     */
    public function receive($maxMessages = null, $timeout = null, \Zend_Queue $queue = null)
    {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }
        if ($timeout === null) {
            $timeout = self::RECEIVE_TIMEOUT_DEFAULT;
        }
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $msgs      = array();
        $info      = $this->_messageTable->info();
        $microtime = microtime(true); // cache microtime
        $db        = $this->_messageTable->getAdapter();

        // start transaction handling
        try {
            if ( $maxMessages > 0 ) { // ZF-7666 LIMIT 0 clause not included.
                $db->beginTransaction();

                $query = $db->select();
                if ($this->_options['options'][\Zend_Db_Select::FOR_UPDATE]) {
                    // turn on forUpdate
                    $query->forUpdate();
                }
                $query->from($info['name'], array('*'))
                    ->where('queue_id=?', $this->getQueueId($queue->getName()))
                    ->where('handle IS NULL OR timeout+' . (int)$timeout . ' < ' . (int)$microtime)
                    ->limit($maxMessages);

                foreach ($db->fetchAll($query) as $data) {
                    // setup our changes to the message
                    $data['handle'] = md5(uniqid(rand(), true));

                    $update = array(
                        'handle'  => $data['handle'],
                        'timeout' => $microtime,
                    );

                    // update the database
                    $where   = array();
                    $where[] = $db->quoteInto('message_id=?', $data['message_id']);
                    $where[] = 'handle IS NULL OR timeout+' . (int)$timeout . ' < ' . (int)$microtime;

                    $count = $db->update($info['name'], $update, $where);

                    // we check count to make sure no other thread has gotten
                    // the rows after our select, but before our update.
                    if ($count > 0) {
                        $msgs[] = $data;
                    }
                }
                $db->commit();
            }
        } catch (\Exception $e) {
            $db->rollBack();

            #require_once 'Zend/Queue/Exception.php';
            throw new \Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
        }

        $options = array(
            'queue'        => $queue,
            'data'         => $msgs,
            'messageClass' => $queue->getMessageClass(),
        );

        return $this->iteratorFactory->create(['options' => $options]);
    }

    /**
     * @inheritdoc
     */
    public function send($message, \Zend_Queue $queue = null)
    {
        if ($this->_messageRow === null) {
            $this->_messageRow = $this->_messageTable->createRow();
        }

        if ($queue === null) {
            $queue = $this->_queue;
        }

        if (is_scalar($message)) {
            $message = (string) $message;
        }
        if (is_string($message)) {
            $message = trim($message);
        }

        if (!$this->isExists($queue->getName())) {
            #require_once 'Zend/Queue/Exception.php';
            throw new \Zend_Queue_Exception('Queue does not exist:' . $queue->getName());
        }

        $msg           = clone $this->_messageRow;
        $msg->queue_id = $this->getQueueId($queue->getName());
        $msg->created  = time();
        $msg->body     = $message;
        $msg->md5      = md5($message);
        // $msg->timeout = ??? @TODO

        try {
            $msg->save();
        } catch (\Exception $e) {
            #require_once 'Zend/Queue/Exception.php';
            throw new \Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
        }

        $options = array(
            'queue' => $queue,
            'data'  => $msg->toArray(),
        );


        return $this->messageFactory->create(['options' => $options]);
    }
}