<?php

use yii\db\Migration;

/**
 * Class m240416_164259_add_voucher_code_to_booking_and_trip_table
 */
class m240416_164259_add_voucher_code_to_booking_and_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%trip}}', 'voucher', $this->string()->after('driver_id_created'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240416_164259_add_voucher_code_to_booking_and_trip_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240416_164259_add_voucher_code_to_booking_and_trip_table cannot be reverted.\n";

        return false;
    }
    */
}
