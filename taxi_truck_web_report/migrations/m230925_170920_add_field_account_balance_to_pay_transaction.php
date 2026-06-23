<?php

use yii\db\Migration;

/**
 * Class m230925_170920_add_field_account_balance_to_pay_transaction
 */
class m230925_170920_add_field_account_balance_to_pay_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pay_transaction', 'account_balance_before', $this->integer()->after('money'));
        $this->addColumn('pay_transaction', 'account_balance_after', $this->integer()->after('account_balance_before'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pay_transaction', 'account_balance_before');
        $this->dropColumn('pay_transaction', 'account_balance_after');
    }
}
