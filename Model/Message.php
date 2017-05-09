<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Model;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    public function __construct(
        array $options = array(),
        ObjectManagerInterface $objectManager
    ) {
        parent::__construct($options);
        $this->objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $data = \Zend_Json::decode($this->body);
        return call_user_func_array(
            [$this->objectManager->create($data['model']), 'execute'],
            $data['parameters']
        );
    }
}