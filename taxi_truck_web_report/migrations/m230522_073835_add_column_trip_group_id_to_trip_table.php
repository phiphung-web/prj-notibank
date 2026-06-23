<?php

use yii\db\Migration;

/**
 * Class m230522_073835_add_column_trip_group_id_to_trip_table
 */
class m230522_073835_add_column_trip_group_id_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'trip_group_id', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'trip_group_id');

        return false;
    }
}
