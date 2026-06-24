<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%booking}}`.
 */
class m230914_044828_add_agency_id_column_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'agency_id', $this->bigInteger()->after('admin_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
