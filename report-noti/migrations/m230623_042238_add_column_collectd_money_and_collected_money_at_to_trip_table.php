<?php

use yii\db\Migration;

/**
 * Class m230623_042238_add_column_collectd_money_and_collected_money_at_to_trip_table
 */
class m230623_042238_add_column_collectd_money_and_collected_money_at_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'collected_money', $this->boolean()->defaultValue(true));
        $this->addColumn('trip', 'collected_money_at', $this->datetime()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'collected_money');
        $this->dropColumn('trip', 'collected_money_at');

        return false;
    }
}
