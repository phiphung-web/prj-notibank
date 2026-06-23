<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%booking}}`.
 */
class m230913_065458_add_admin_id_column_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'admin_id', $this->bigInteger()->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
