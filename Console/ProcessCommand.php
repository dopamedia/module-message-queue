<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Console;

use Dopamedia\MessageQueue\Model\Processor;
use \Magento\Backend\App\Area\FrontNameResolver;
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
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @inheritDoc
     */
    public function __construct(\Magento\Framework\App\State $state, Processor $processor, $name = null)
    {
        $this->processor = $processor;
        parent::__construct($name);
        $this->state = $state;
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

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->state->setAreaCode(FrontNameResolver::AREA_CODE);
    }

}