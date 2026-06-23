<?php

use yii\db\Migration;

/**
 * Class m230930_042708_add_field_delete_to_pay_transaction_table
 */
class m230930_042708_add_field_delete_to_pay_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_transaction', 'is_disabled', $this->tinyInteger()->defaultValue(0)->comment('0: Bình thường | 1: Đã xóa'));
        $this->addColumn('pay_transaction', 'disabled_on', $this->datetime()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_transaction', 'is_disabled');
        $this->dropColumn('pay_transaction', 'disabled_on');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230930_042708_add_field_delete_to_pay_transaction_table cannot be reverted.\n";

        return false;
    }
    */
}
