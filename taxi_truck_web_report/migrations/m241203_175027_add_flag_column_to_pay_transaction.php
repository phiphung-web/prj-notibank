<?php

use yii\db\Migration;

/**
 * Class m241203_175027_add_flag_column_to_pay_transaction
 */
class m241203_175027_add_flag_column_to_pay_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%pay_transaction}}', 'flag', $this->integer()->defaultValue(0)->notNull()->comment('Flag for transaction'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%pay_transaction}}', 'flag');
    }
}
