<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip}}`.
 */
class m230920_041213_add_change_column_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('trip', 'driver_debt_collection', 'driver_debt');
        $this->dropColumn('trip', 'driver_debt_settlement');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('trip', 'driver_debt', 'driver_debt_collection');
        $this->addColumn('trip', 'driver_debt_settlement', $this->tinyInteger()->defaultValue(1));
    }
}
