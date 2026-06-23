<?php

use yii\db\Migration;

/**
 * Class m231023_175328_add_field_to_driver_table
 */
class m231023_175328_add_field_to_driver_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('driver', 'birthday', $this->date());
        $this->addColumn('driver', 'address', $this->text());
        $this->addColumn('driver', 'driver_rank', $this->string(50));
        $this->addColumn('driver', 'total_trip_bid', $this->integer()->defaultValue(0));
        $this->addColumn('driver', 'total_complete', $this->integer()->defaultValue(0));
        $this->addColumn('driver', 'total_cancel', $this->integer()->defaultValue(0));
        $this->addColumn('driver', 'total_recharge', $this->integer()->defaultValue(0));
        $this->addColumn('driver', 'total_revenue', $this->integer()->defaultValue(0));
        $this->addColumn('driver', 'status', $this->tinyInteger()->defaultValue(1)->comment('0: Người mới | 1: Đang hoạt động | 2: Khóa'));
        $this->addColumn('driver', 'register', $this->tinyInteger()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('driver', 'birthday');
        $this->dropColumn('driver', 'address');
        $this->dropColumn('driver', 'driver_rank');
        $this->dropColumn('driver', 'total_trip_bid');
        $this->dropColumn('driver', 'total_complete');
        $this->dropColumn('driver', 'total_cancel');
        $this->dropColumn('driver', 'total_recharge');
        $this->dropColumn('driver', 'total_revenue');
        $this->dropColumn('driver', 'status');
        $this->dropColumn('driver', 'register');

        return false;
    }
}
