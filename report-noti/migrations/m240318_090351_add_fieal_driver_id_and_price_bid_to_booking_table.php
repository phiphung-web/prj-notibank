<?php

use yii\db\Migration;

/**
 * Class m240318_090351_add_fieal_driver_id_and_price_bid_to_booking_table
 */
class m240318_090351_add_fieal_driver_id_and_price_bid_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'driver_id_created', $this->bigInteger()->defaultValue(0)->after('agency_id'));
        $this->addColumn('booking', 'price_bid', $this->integer()->defaultValue(0)->after('price_customer'));
        $this->addColumn('trip', 'driver_id_created', $this->bigInteger()->defaultValue(0)->after('agency_id'));
        $this->addColumn('trip', 'paid_driver', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240318_090351_add_fieal_driver_id_and_price_bid_to_booking_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240318_090351_add_fieal_driver_id_and_price_bid_to_booking_table cannot be reverted.\n";

        return false;
    }
    */
}
