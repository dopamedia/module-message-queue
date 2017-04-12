<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 12.01.17
 */

namespace Dopamedia\MessageQueue\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Queue extends \Zend_Queue
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var Queue\Adapter\DbFactory
     */
    protected $adapterFactory;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Queue\Adapter\DbFactory $adapterFactory
     * @param string $queueName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        Queue\Adapter\DbFactory $adapterFactory,
        $queueName = 'default'
    ) {

        $this->resource = $resource;
        $this->adapterFactory = $adapterFactory;

        $config = [
            'name' => $queueName,
            'messageClass' => Message::class,
            'dbAdapter' => $resource->getConnection(),
            'dbQueueTable' => $resource->getTableName('queue'),
            'dbMessageTable' => $resource->getTableName('queue_message'),
        ];

        parent::__construct($config);

        /** @var Queue\Adapter\Db $adapter */
        $adapter = $this->adapterFactory->create(['options' => $this->getOptions()]);
        $this->setAdapter($adapter);
    }

    /**
     * @SuppressWarnings(PHPMD)
     *
     * @param string $name
     * @param null $timeout
     * @throws LocalizedException
     * @return void
     */
    public function createQueue($name, $timeout = null)
    {
        throw new LocalizedException(
            new Phrase("Do not use this function, use the constructor instead")
        );
    }

    /**
     * @param string $model
     * @param array $parameters
     * @return \Zend_Queue_Message
     */
    public function addTask($model, array $parameters = [])
    {
        $body = \Zend_Json::encode([
            'model' => $model,
            'parameters' => $parameters
        ]);
        return $this->send($body);
    }
}