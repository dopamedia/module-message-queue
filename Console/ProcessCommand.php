<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Console;

use Dopamedia\MessageQueue\Model\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCommand extends Command
{
    const COMMAND_QUEUE_PROCESS = 'message-queue:process';

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @inheritDoc
     */
    public function __construct(Processor $processor, $name = null)
    {
        $this->processor = $processor;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->processor->processQueue();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_PROCESS);
        parent::configure();
    }
}