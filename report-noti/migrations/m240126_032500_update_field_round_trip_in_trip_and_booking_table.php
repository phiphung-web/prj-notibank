<?php

use yii\db\Migration;

/**
 * Class m240126_032500_update_field_round_trip_in_trip_and_booking_table
 */
class m240126_032500_update_field_round_trip_in_trip_and_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('trip', 'round_trip', $this->tinyInteger()->defaultValue(0));
        $this->alterColumn('booking', 'round_trip', $this->tinyInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('trip', 'round_trip', $this->boolean());
        $this->alterColumn('booking', 'round_trip', $this->boolean());

        return false;
    }
}
