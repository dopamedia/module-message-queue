<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 14.01.17
 */

namespace Dopamedia\MessageQueue\Test\Unit\Model;

use Dopamedia\MessageQueue\Model\Queue;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Dopamedia\MessageQueue\Model\Queue\Adapter\Db
     */
    protected $adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Dopamedia\MessageQueue\Model\Queue\Adapter\DbFactory
     */
    protected $adapterFactoryMock;

    /**
     * @var Queue
     */
    protected $queue;

    protected function setUp()
    {
        $this->resource = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapterMock = $this->getMockBuilder('Dopamedia\MessageQueue\Model\Queue\Adapter\Db')
            ->disableOriginalConstructor()
            ->setMethods(['send', 'isExists', 'create'])
            ->getMock();

        $this->adapterFactoryMock = $this->getMockBuilder('Dopamedia\MessageQueue\Model\Queue\Adapter\DbFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->adapterFactoryMock->expects($this->any())->method('create')->willReturn($this->adapterMock);

        $this->queue = new Queue(
            $this->resource,
            $this->adapterFactoryMock
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCreateQueueThrowsLocalizedException()
    {
        $this->queue->createQueue('name');
    }

    public function testAddTask()
    {
        $this->adapterMock->expects($this->once())->method('send')->willReturn(new \Zend_Queue_Message());

        $this->assertInstanceOf(
            \Zend_Queue_Message::class,
            $this->queue->addTask('\The\Callback')
        );
    }
}
