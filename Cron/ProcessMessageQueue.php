<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Cron;

use Dopamedia\MessageQueue\Model\Processor;

class ProcessMessageQueue
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @param Processor $processor
     */
    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->processor->processQueue();
    }
}