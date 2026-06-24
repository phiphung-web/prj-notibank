<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pay_transaction}}`.
 */
class m230905_085059_add_column_to_pay_transaction_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%pay_transaction}}', 'message', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%pay_transaction}}', 'message');
    }
}
