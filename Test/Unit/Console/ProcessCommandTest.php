<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Test\Unit\Console;

use Dopamedia\MessageQueue\Console\ProcessCommand;
use Symfony\Component\Console\Tester\CommandTester;

class ProcessCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Dopamedia\MessageQueue\Model\Processor
     */
    protected $processorMock;

    /**
     * @var ProcessCommand
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    protected function setUp()
    {
        $this->processorMock = $this->getMockBuilder('Dopamedia\MessageQueue\Model\Processor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new ProcessCommand($this->processorMock);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testGetOptions()
    {
        $this->assertSame(
            ProcessCommand::COMMAND_QUEUE_PROCESS,
            $this->command->getName()
        );
    }

    public function testExecute()
    {
        $this->processorMock->expects($this->once())
            ->method('processQueue');
        $this->commandTester->execute([]);
    }

}
