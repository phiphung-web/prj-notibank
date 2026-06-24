<?php

use yii\db\Migration;

/**
 * Class m241103_082239_add_money_fields_to_trip_table
 */
class m241103_082239_add_money_fields_to_trip_table extends Migration
{
    /**
    * {@inheritdoc}
    */
    public function safeUp()
    {
        $this->addColumn('{{%trip}}', 'money_customer_deposit', $this->integer()->defaultValue(0)->comment('Customer Deposit'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%trip}}', 'money_customer_deposit');
    }
}
