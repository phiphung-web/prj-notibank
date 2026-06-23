<?php

use yii\db\Migration;

/**
 * Class m240728_174352_add_paid_driver_on_to_trip_table
 */
class m240728_174352_add_paid_driver_on_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('trip', 'paid_driver', 'paid_driver_on');
        $this->alterColumn('trip', 'paid_driver_on', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
