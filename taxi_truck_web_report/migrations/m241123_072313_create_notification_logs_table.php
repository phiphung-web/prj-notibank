<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification_logs}}`.
 */
class m241123_072313_create_notification_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification_logs}}', [
            'id' => $this->primaryKey(),
            'trip_id' => $this->integer(),
            'user_id' => $this->integer(),
            'driver_id' => $this->integer(),
            'type' => $this->tinyInteger(1)->defaultValue(0),
            'title' => $this->text(),
            'message' => $this->text(),
            'message_data' => $this->text(),
            'status' => $this->tinyInteger(1)->defaultValue(0),
            'created_on' => $this->integer(),
            'updated_on' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%notification_logs}}');
    }
}
