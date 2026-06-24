<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%request_call_back}}`.
 */
class m230908_034115_create_request_call_back_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%request_call_back}}', [
          'id' => $this->bigPrimaryKey(),
          'phone' => $this->string()->notNull(),
          'status' => $this->tinyInteger()->defaultValue(0)->notNull()->comment('0: Chờ xử lý | 1: Xác nhận | 2: Hủy'),
          'type_reject' => $this->integer()->defaultValue(null),
          'note' => $this->text()->null(),
          'created_on' => $this->datetime()->defaultValue(null),
          'modified_on' => $this->datetime()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%request_call_back}}');
    }
}
