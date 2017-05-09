<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 09.05.17
 */

namespace Dopamedia\MessageQueue\Model;

class Iterator extends \Zend_Queue_Message_Iterator
{
    /**
     * @inheritDoc
     */
    public function __construct(
        array $options = array(),
        \Dopamedia\MessageQueue\Model\MessageFactory $messageFactory
    ) {

        if (isset($options['queue'])) {
            $this->_queue      = $options['queue'];
            $this->_queueClass = get_class($this->_queue);
            $this->_connected  = true;
        } else {
            $this->_connected = false;
        }
        if (isset($options['messageClass'])) {
            $this->_messageClass = $options['messageClass'];
        }

        if (!is_array($options['data'])) {
            #require_once 'Zend/Queue/Exception.php';
            throw new \Zend_Queue_Exception('array optionsuration must have $options[\'data\'] = array');
        }

        // load the message class
        $classname = $this->_messageClass;
        if (!class_exists($classname)) {
            #require_once 'Zend/Loader.php';
            \Zend_Loader::loadClass($classname);
        }

        // for each of the messages
        foreach ($options['data'] as $data) {
            // construct the message parameters
            $message = array('data' => $data);

            // If queue has not been set, then use the default.
            if (empty($message['queue'])) {
                $message['queue'] = $this->_queue;
            }

            // construct the message and add it to _data[];
            $this->_data[] = $messageFactory->create(['options' => $message]);
        }
    }
}