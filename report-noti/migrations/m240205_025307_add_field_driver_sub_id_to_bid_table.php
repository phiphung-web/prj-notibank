<?php

use yii\db\Migration;

/**
 * Class m240205_025307_add_field_driver_sub_id_to_bid_table
 */
class m240205_025307_add_field_driver_sub_id_to_bid_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('bid', 'driver_sub_id', $this->bigInteger(2)->defaultValue(0)->after('driver_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('bid', 'driver_sub_id');

        return false;
    }
}
