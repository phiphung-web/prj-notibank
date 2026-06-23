<?php

use yii\db\Migration;

/**
 * Class m240112_142409_add_source_trip_to_trip_table
 */
class m240112_142409_add_source_trip_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'source_trip', $this->tinyInteger(2)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'source_trip');

        return false;
    }
}
