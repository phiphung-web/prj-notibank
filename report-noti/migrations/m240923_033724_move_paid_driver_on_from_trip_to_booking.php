<?php

use yii\db\Migration;

/**
 * Class m240923_033724_move_paid_driver_on_from_trip_to_booking
 */
class m240923_033724_move_paid_driver_on_from_trip_to_booking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('trip', 'paid_driver_on');
        $this->addColumn('booking', 'paid_driver_on', $this->dateTime()->null()->comment('Ngày thanh toán cho lái xe'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240923_033724_move_paid_driver_on_from_trip_to_booking cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240923_033724_move_paid_driver_on_from_trip_to_booking cannot be reverted.\n";

        return false;
    }
    */
}
