<?php

use yii\db\Migration;

/**
 * Class m240615_090351_add_payment_method_to_booking_table
 */
class m240615_090351_add_payment_method_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = $this->db->schema->getTableSchema('{{%booking}}');
        $this->addColumn('{{%booking}}', 'payment_method', $this->tinyInteger(1)->defaultValue(0)->after('price_bid'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240615_090351_add_payment_method_to_booking_table cannot be reverted.\n";

        return false;
    }
}
