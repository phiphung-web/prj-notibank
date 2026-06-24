<?php

use yii\db\Migration;

/**
 * Class m231104_102257_add_field_latest_trip_to_customer_table
 */
class m231104_102257_add_field_latest_trip_to_customer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('customer', 'lastest_trip', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('customer', 'lastest_trip');

        return false;
    }
}
