<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Model;

class Processor
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @param Queue $queue
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Queue $queue,
        QueueFactory $queueFactory
    ) {
        $this->queue = $queue;
        $this->queueFactory = $queueFactory;
    }

    /**
     * @SuppressWarnings(PHPMD)
     *
     * @return array
     */
    public function processQueue()
    {
        $startTime = microtime(true);
        $maxRuntime = 60;
        $queueNames = $this->queue->getQueues();

        /** @var Queue[] $queues */
        $queues = [];
        foreach ($queueNames as $queueName) {
            /** @var Queue $tmpQueue */
            $tmpQueue = $this->queueFactory->create(['queueName' => $queueName]);
            if ($tmpQueue->count()) {
                $queues[$queueName] = $tmpQueue;
            }
        }
        $statistics = [];

        while (((microtime(true) - $startTime) < $maxRuntime) && count($queues)) {
            foreach ($queues as $queueName => $queue) {
                $messages = $queue->receive(1);
                if (count($messages) > 0) {
                    foreach ($messages as $message) {
                        /* @var $message Message */
                        $message->execute();
                        $queue->deleteMessage($message);
                        if (empty($statistics[$queueName])) {
                            $statistics[$queueName] = 0;
                        }
                        $statistics[$queueName]++;
                        if ((microtime(true) - $startTime) > $maxRuntime) {
                            return $statistics;
                        }
                    }
                } else {
                    unset($queues[$queueName]);
                }
            }
        }

        return $statistics;
    }
}