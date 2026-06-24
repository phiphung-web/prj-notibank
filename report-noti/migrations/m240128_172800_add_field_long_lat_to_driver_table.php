<?php

use yii\db\Migration;

/**
 * Class m240128_172800_add_field_long_lat_to_driver_table
 */
class m240128_172800_add_field_long_lat_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'latitude', $this->double()->defaultValue(0)->after('parent_id'));
        $this->addColumn('driver', 'longitude', $this->double()->defaultValue(0)->after('latitude'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'latitude');
        $this->dropColumn('driver', 'longitude');

        return false;
    }
}
