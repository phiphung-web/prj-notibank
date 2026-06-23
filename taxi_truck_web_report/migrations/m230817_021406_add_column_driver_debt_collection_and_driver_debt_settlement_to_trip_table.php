<?php

use yii\db\Migration;

/**
 * Class m230817_021406_add_column_driver_debt_collection_and_driver_debt_settlement_to_trip_table
 */
class m230817_021406_add_column_driver_debt_collection_and_driver_debt_settlement_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add a new column 'driver_debt_collection' to the 'trip' table
        // Set the default value to 1 (representing 'already settled')
        $this->addColumn('trip', 'driver_debt_collection', $this->tinyInteger()->defaultValue(1));
        // Add another new column 'driver_debt_settlement' to the 'trip' table
        // Set the default value to 1 (representing 'already settled')
        $this->addColumn('trip', 'driver_debt_settlement', $this->tinyInteger()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'driver_debt_collection');
        $this->dropColumn('trip', 'driver_debt_settlement');
    }
}
