<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%customer_service}}`.
 */
class m240106_040911_create_customer_service_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%customer_service}}', [
            'id' => $this->primaryKey(),
            'trip_id' => $this->bigInteger()->notNull(),
            'customer_id' => $this->bigInteger(),
            'driver_id' => $this->bigInteger(),
            'type' => $this->tinyInteger(5),
            'cus_feedback_trip' => $this->text(),
            'cus_feedback_driver' => $this->text(),
            'driver_feedback_cus' => $this->text(),
            'status' => $this->tinyInteger(5)->defaultValue(0),
            'point' => $this->tinyInteger(5)->defaultValue(0),
            'userid_created' => $this->integer(),
            'userid_updated' => $this->integer(),
            'created_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('customer_service');
    }
}
