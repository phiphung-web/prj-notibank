<?php

use yii\db\Migration;

/**
 * Class m230605_023336_add_column_sell_start_time_to_trip_table
 */
class m230605_023336_add_column_sell_start_time_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'sell_start_time', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'sell_start_time');
    }
}
