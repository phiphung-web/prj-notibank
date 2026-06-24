<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%booking}}`.
 */
class m230524_033803_create_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%booking}}', [
            'id' => $this->primaryKey(),
            'pickup_address' => $this->string(),
            'destination_address' => $this->string(),
            'round_trip' => $this->boolean(),
            'is_have_bill' => $this->boolean(),
            'customer_name' => $this->string(),
            'customer_phone' => $this->string(),
            'pickup_time' => $this->dateTime(),
            'type_of_car' => $this->string(),
            'note' => $this->string(),
            'status' => $this->string(),
            'created_on' => $this->datetime()->defaultValue(null),
            'modified_on' => $this->datetime()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%booking}}');
    }
}
