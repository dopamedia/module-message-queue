<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Model;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @method Queue getQueueClass()
 *
 * Class Message
 * @package Dopamedia\MessageQueue\Model
 */
class Message extends \Zend_Queue_Message
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $data = \Zend_Json::decode($this->body);
        return call_user_func_array([$data['model'], 'execute'], $data['parameters']);
    }
}