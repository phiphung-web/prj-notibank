<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%driver_token}}`.
 */
class m241214_081317_create_driver_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%driver_token}}', [
            'id' => $this->primaryKey(),
            'driver_id' => $this->integer()->notNull(),
            'token' => $this->string(500)->notNull(),
            'os' => $this->tinyInteger()->defaultValue(0),
            'uuid' => $this->string(50),
            'type' => $this->tinyInteger()->defaultValue(0),
            'created_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'expired_on' => $this->timestamp()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%driver_token}}');
    }
}
