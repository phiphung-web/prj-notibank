<?php

use yii\db\Migration;

/**
 * Class m241127_191151_add_field_to_bank_transaction
 */
class m241127_191151_add_field_to_bank_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('bank_transaction', 'account_holder', $this->text());
        $this->addColumn('bank_transaction', 'account_number', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241127_191151_add_field_to_bank_transaction cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241127_191151_add_field_to_bank_transaction cannot be reverted.\n";

        return false;
    }
    */
}
