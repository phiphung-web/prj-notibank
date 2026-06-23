<?php

use yii\db\Migration;

class m250522_080000_update_round_trip_values extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Update trip table - round_trip column
        $this->update('trip', ['round_trip' => 1], ['round_trip' => 1]);
        $this->update('trip', ['round_trip' => 1], ['round_trip' => 2]);
        $this->update('trip', ['round_trip' => 1], ['round_trip' => 3]);
        $this->update('trip', ['round_trip' => 2], ['round_trip' => 4]);
        $this->update('trip', ['round_trip' => 2], ['round_trip' => 5]);
        $this->update('trip', ['round_trip' => 2], ['round_trip' => 6]);

        // Update booking table - round_trip column
        $this->update('booking', ['round_trip' => 1], ['round_trip' => 1]);
        $this->update('booking', ['round_trip' => 1], ['round_trip' => 2]);
        $this->update('booking', ['round_trip' => 1], ['round_trip' => 3]);
        $this->update('booking', ['round_trip' => 2], ['round_trip' => 4]);
        $this->update('booking', ['round_trip' => 2], ['round_trip' => 5]);
        $this->update('booking', ['round_trip' => 2], ['round_trip' => 6]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {}
}
