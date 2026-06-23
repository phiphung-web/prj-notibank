<?php

use yii\db\Migration;

/**
 * Class m250117_161702_add_money_fields_to_bid_and_trip_return
 */
class m250117_161702_add_money_fields_to_bid_and_trip_return extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Thêm cột money_before và money_after vào bảng bid
        $this->addColumn('{{%bid}}', 'money_before', $this->integer()->defaultValue(0)->comment('Money before transaction'));
        $this->addColumn('{{%bid}}', 'money_after', $this->integer()->defaultValue(0)->comment('Money after transaction'));

        // Thêm cột money_before và money_after vào bảng trip_return
        $this->addColumn('{{%trip_return}}', 'money_before', $this->integer()->defaultValue(0)->comment('Money before transaction'));
        $this->addColumn('{{%trip_return}}', 'money_after', $this->integer()->defaultValue(0)->comment('Money after transaction'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Xóa cột money_before và money_after từ bảng bid
        $this->dropColumn('{{%bid}}', 'money_before');
        $this->dropColumn('{{%bid}}', 'money_after');

        // Xóa cột money_before và money_after từ bảng trip_return
        $this->dropColumn('{{%trip_return}}', 'money_before');
        $this->dropColumn('{{%trip_return}}', 'money_after');
    }
}
