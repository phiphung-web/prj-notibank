<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bank_transaction}}`.
 */
class m241026_050254_create_bank_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bank_transaction}}', [
            'id' => $this->primaryKey(),
            'type_bank' => $this->tinyInteger()->notNull()->comment('Loại ngân hàng'),
            'admin_id' => $this->integer()->notNull()->comment('ID của admin'),
            'check_driver' => $this->tinyInteger()->defaultValue(0)->comment('Nạp tiền lái xe'),
            'token_tele' => $this->string(255)->null()->comment('Token Telegram'),
            'chat_tele' => $this->string(255)->null()->comment('Chat ID Telegram'),
            'account_balance' => $this->integer()->defaultValue(0)->comment('Số dư tài khoản'),
            'created_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Thời gian tạo'),
            'updated_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Thời gian cập nhật'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bank_transaction}}');
    }
}
