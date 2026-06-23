<?php

use yii\db\Migration;

/**
 * Class m230606_033612_add_column_is_sell_time_to_trip_table
 */
class m230606_033612_add_column_is_sell_time_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'is_sell_time', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'is_sell_time');
    }
}
