<?php

use yii\db\Migration;

/**
 * Class m240113_044402_add_some_field_to_trip_group_table
 */
class m240113_044402_add_some_field_to_trip_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip_group', 'vehicle_type', $this->string(255));
        $this->addColumn('trip_group', 'license_plates', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip_group', 'license_plates');
        $this->dropColumn('trip_group', 'vehicle_type');

        return false;
    }
}
