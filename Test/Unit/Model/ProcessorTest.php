<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Test\Unit\Model;

use Dopamedia\MessageQueue\Model\Processor;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Dopamedia\MessageQueue\Model\QueueFactory
     */
    protected $queueFactoryMock;

    protected function setUp()
    {
        $this->queueFactoryMock = $this->getMockBuilder('Dopamedia\MessageQueue\Model\QueueFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    public function testProcessQueue()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Dopamedia\MessageQueue\Model\Queue $queueMock */
        $queueMock = $this->getMockBuilder('Dopamedia\MessageQueue\Model\Queue')
            ->disableOriginalConstructor()
            ->getMock();

        $queueMock
            ->method('getQueues')
            ->willReturn(['tmp_queue']);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Dopamedia\MessageQueue\Model\Queue $queueMock */
        $tmpQueue = $this->getMockBuilder('Dopamedia\MessageQueue\Model\Queue')
            ->disableOriginalConstructor()
            ->getMock();

        $tmpQueue->method('count')
            ->willReturn(1);

        $tmpMessage = $this->getMockBuilder('Zend_Queue_Message')
            ->disableOriginalConstructor()
            ->setMethods(['execute', 'deleteMessage'])
            ->getMock();

        $tmpQueue
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn([$tmpMessage]);

        $this->queueFactoryMock
            ->method('create')
            ->willReturn($tmpQueue);

        $processor = new Processor(
            $queueMock,
            $this->queueFactoryMock
        );

        $expected = [
            'tmp_queue' => 1
        ];

        $this->assertSame($expected, $processor->processQueue());
    }
}
