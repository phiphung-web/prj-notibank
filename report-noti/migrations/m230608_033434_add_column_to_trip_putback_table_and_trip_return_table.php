<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip_putback_table_and_trip_return}}`.
 */
class m230608_033434_add_column_to_trip_putback_table_and_trip_return_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip_putback', 'note', $this->text());
        $this->addColumn('trip_return', 'note', $this->text());
        $this->addColumn('trip_return', 'trip_id', $this->bigInteger());
        $this->addColumn('trip_return', 'driver_id', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip_putback', 'note');
        $this->dropColumn('trip_return', 'note');
        $this->dropColumn('trip_return', 'trip_id');
        $this->dropColumn('trip_return', 'driver_id');
    }
}
