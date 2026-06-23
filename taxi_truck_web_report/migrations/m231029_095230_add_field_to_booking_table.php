<?php

use yii\db\Migration;

/**
 * Class m231029_095230_add_field_to_booking_table
 */
class m231029_095230_add_field_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'utm_source', $this->text());
        $this->addColumn('booking', 'utm_campaign', $this->text());
        $this->addColumn('booking', 'utm_medium', $this->text());
        $this->addColumn('booking', 'remote_ip', $this->text());
        $this->addColumn('booking', 'url', $this->text());
        $this->addColumn('booking', 'voucher', $this->text());
        $this->addColumn('booking', 'stop_point', $this->text());
        $this->addColumn('booking', 'tracking_info', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('booking', 'utm_source');
        $this->dropColumn('booking', 'utm_campaign');
        $this->dropColumn('booking', 'utm_medium');
        $this->dropColumn('booking', 'remote_ip');
        $this->dropColumn('booking', 'url');
        $this->dropColumn('booking', 'voucher');
        $this->dropColumn('booking', 'stop_point');
        $this->dropColumn('booking', 'tracking_info');
    }
}
