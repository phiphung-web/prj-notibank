<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%summary_report}}`.
 */
class m231009_144927_create_summary_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%summary_report}}', [
            'id' => $this->primaryKey(11),
            'dt_date' => $this->date(),
            'total_booking' => $this->integer(11)->defaultValue(0),
            'total_booking_cancel' => $this->integer(11)->defaultValue(0),
            'total_booking_waiting' => $this->integer(11)->defaultValue(0),
            'total_booking_create' => $this->integer(11)->defaultValue(0),
            'total_booking_confirm' => $this->integer(11)->defaultValue(0),
            'total_trip' => $this->integer(11)->defaultValue(0),
            'total_trip_cancel' => $this->integer(11)->defaultValue(0),
            'total_trip_complete' => $this->integer(11)->defaultValue(0),
            'total_trip_done' => $this->integer(11)->defaultValue(0),
            'total_trip_pending' => $this->integer(11)->defaultValue(0),
            'total_trip_create' => $this->integer(11)->defaultValue(0),
            'mail_source' => $this->integer(11)->defaultValue(0),
            'call_source' => $this->integer(11)->defaultValue(0),
            'facebook_source' => $this->integer(11)->defaultValue(0),
            'comeback_source' => $this->integer(11)->defaultValue(0),
            'agency_source' => $this->integer(11)->defaultValue(0),
            'zalo_oa_source' => $this->integer(11)->defaultValue(0),
            'customer_price' => $this->integer(11)->defaultValue(0),
            'driver_price' => $this->integer(11)->defaultValue(0),
            'revenue' => $this->integer(11)->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%summary_report}}');
    }
}
