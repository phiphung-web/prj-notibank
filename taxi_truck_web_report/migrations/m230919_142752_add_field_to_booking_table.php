<?php

use yii\db\Migration;

/**
 * Class m230919_142752_add_field_to_booking_table
 */
class m230919_142752_add_field_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'price_customer', $this->integer()->after('customer_phone'));
        $this->addColumn('booking', 'is_collect_money', $this->boolean()->after('price_customer'));
        $this->addColumn('booking', 'area', $this->string(255)->after('pickup_address'));
        $this->alterColumn('booking', 'type', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('booking', 'price_customer');
        $this->dropColumn('booking', 'is_collect_money');
        $this->dropColumn('booking', 'area');
        $this->alterColumn('booking', 'type', $this->tinyInteger());
    }
}
