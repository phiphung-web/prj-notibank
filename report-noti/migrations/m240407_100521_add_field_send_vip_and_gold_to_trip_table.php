<?php

use yii\db\Migration;

/**
 * Class m240407_100521_add_field_send_vip_and_gold_to_trip_table
 */
class m240407_100521_add_field_send_vip_and_gold_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'send_vip', $this->boolean()->defaultValue(0)->after('status'));
        $this->addColumn('trip', 'send_gold', $this->boolean()->defaultValue(0)->after('status'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
