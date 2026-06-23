<?php

use yii\db\Migration;

/**
 * Class m231003_101246_add_column_call_driver_to_trip_table
 */
class m231003_101246_add_column_call_driver_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'call_driver', $this->tinyInteger()->defaultValue(null)->comment('0: Chưa gọi | 1: Đã gọi'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'call_driver');
    }
}
