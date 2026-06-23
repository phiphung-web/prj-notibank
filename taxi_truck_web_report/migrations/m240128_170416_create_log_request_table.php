<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%log_request}}`.
 */
class m240128_170416_create_log_request_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%log_request}}', [
            'id' => $this->primaryKey(),
            'driver_id' => $this->bigInteger(),
            'status' => $this->integer(),
            'message' => $this->string(),
            'acceptedOn' => $this->dateTime(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%log_request}}');
    }
}
