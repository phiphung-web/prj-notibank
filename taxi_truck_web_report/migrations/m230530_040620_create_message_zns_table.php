<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%messages_zns}}`.
 */
class m230530_040620_create_message_zns_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%message_zns}}', [
            'id' => $this->primaryKey(),
            'trip_id' => $this->bigInteger(),
            'template_id' => $this->integer(),
            'phone' => $this->string(50),
            'code' => $this->integer(),
            'message' => $this->string(),
            'template_data' => $this->text(),
            'created_on' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%messages_zns}}');
    }
}
