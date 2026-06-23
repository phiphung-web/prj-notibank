<?php

use yii\db\Migration;

/**
 * Class m240416_155703_create_table_voucher
 */
class m240416_155703_create_table_voucher extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%voucher}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull()->unique(),
            'quantity' => $this->integer()->defaultValue(0),
            'value' => $this->decimal(10, 2)->notNull(),
            'type' => $this->tinyInteger(2)->defaultValue(0),
            'is_send' => $this->boolean()->defaultValue(false),
            'status' => $this->boolean()->defaultValue(false),
            'expired_at' => $this->dateTime(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
