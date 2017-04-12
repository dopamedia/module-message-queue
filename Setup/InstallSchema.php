<?php
/**
 * User: Andreas Penz <office@dopa.media>
 * Date: 08.01.17
 */

namespace Dopamedia\MessageQueue\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD)
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'queue'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('queue')
        )->addColumn(
            'queue_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Queue ID'
        )->addColumn(
            'queue_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Queue name'
        )->addColumn(
            'timeout',
            \Magento\Framework\Db\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 30]
        )->addIndex(
            $installer->getIdxName(
                'queue',
                ['queue_name'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['queue_name'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Queue Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'queue_message'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('queue_message')
        )->addColumn(
            'message_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Message ID'
        )->addColumn(
            'queue_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false]
        )->addColumn(
            'handle',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => true]
        )->addColumn(
            'body',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
            ['nullable' => false]
        )->addColumn(
            'md5',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false]
        )->addColumn(
            'timeout',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            [14, 4],
            ['unsigned' => true, 'nullable' => true]
        )->addColumn(
            'created',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Message topic'
        )->addForeignKey(
            $installer->getFkName('queue_message', 'queue_id', 'queue', 'queue_id'),
            'queue_id',
            $installer->getTable('queue'),
            'queue_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Queue Message Table'
        );
        $installer->getConnection()->createTable($table);
    }
}