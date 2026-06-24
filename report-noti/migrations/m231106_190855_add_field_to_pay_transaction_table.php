<?php

use yii\db\Migration;

/**
 * Class m231106_190855_add_field_to_pay_transaction_table
 */
class m231106_190855_add_field_to_pay_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_transaction', 'money_before', $this->integer());
        $this->addColumn('pay_transaction', 'money_after', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_transaction', 'money_before');
        $this->dropColumn('pay_transaction', 'money_after');
    }
}
