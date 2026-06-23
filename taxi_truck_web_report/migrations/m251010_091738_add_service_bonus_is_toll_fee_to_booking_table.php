<?php

use yii\db\Migration;

class m251010_091738_add_service_bonus_is_toll_fee_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%booking}}', 'service', $this->string(50)->after('round_trip'));
        $this->addColumn('{{%booking}}', 'bonus', $this->integer()->defaultValue(0)->after('service'));
        $this->addColumn('{{%booking}}', 'is_toll_fee', $this->boolean()->defaultValue(false)->after('bonus'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%booking}}', 'is_toll_fee');
        $this->dropColumn('{{%booking}}', 'bonus');
        $this->dropColumn('{{%booking}}', 'service');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251010_091738_add_service_bonus_is_toll_fee_to_booking_table cannot be reverted.\n";

        return false;
    }
    */
}
