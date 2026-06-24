<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip}}`.
 */
class m230915_062524_add_agency_id_column_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'agency_id', $this->bigInteger()->after('booking_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'agency_id');
    }
}
