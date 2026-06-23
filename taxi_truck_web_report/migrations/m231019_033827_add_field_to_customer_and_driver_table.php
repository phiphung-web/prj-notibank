<?php

use yii\db\Migration;

/**
 * Class m231019_033827_add_field_to_customer_and_driver_table
 */
class m231019_033827_add_field_to_customer_and_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('customer', 'customer_rank', $this->string(50));
        $this->addColumn('customer', 'birthday', $this->date());
        $this->addColumn('customer', 'gender', $this->tinyInteger(3)->comment('0: Nam | 1: Nữ'));
        $this->addColumn('customer', 'address', $this->text());
        $this->addColumn('customer', 'total_trip', $this->integer()->defaultValue(0));
        $this->addColumn('customer', 'total_complete', $this->integer()->defaultValue(0));
        $this->addColumn('customer', 'total_cancel', $this->integer()->defaultValue(0));
        $this->addColumn('customer', 'total_paid', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('customer', 'customer_rank');
        $this->dropColumn('customer', 'birthday');
        $this->dropColumn('customer', 'gender');
        $this->dropColumn('customer', 'address');
        $this->dropColumn('customer', 'total_trip');
        $this->dropColumn('customer', 'total_complete');
        $this->dropColumn('customer', 'total_cancel');
        $this->dropColumn('customer', 'total_paid');

        return false;
    }
}
