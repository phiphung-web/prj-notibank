<?php

use yii\db\Migration;

/**
 * Class m240313_040232_add_field_customer_property_to_trip_and_booking_table
 */
class m240313_040232_add_field_customer_property_to_trip_and_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('car', 'car_year', $this->integer()->defaultValue(0));
        $this->addColumn('trip', 'customer_property', $this->tinyInteger()->defaultValue(0));
        $this->addColumn('booking', 'customer_property', $this->tinyInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240313_040232_add_field_customer_property_to_trip_and_booking_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240313_040232_add_field_customer_property_to_trip_and_booking_table cannot be reverted.\n";

        return false;
    }
    */
}
