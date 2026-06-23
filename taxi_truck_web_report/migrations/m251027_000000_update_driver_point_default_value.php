<?php

use yii\db\Migration;

/**
 * Class m251027_000000_update_driver_point_default_value
 * Updates driver point column to have default value of 0 and NOT NULL constraint
 */
class m251027_000000_update_driver_point_default_value extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // First, update existing NULL values to 0
        $this->execute("UPDATE driver SET point = 0 WHERE point IS NULL");

        // Modify the column to have default value 0 and NOT NULL constraint
        $this->alterColumn('driver', 'point', $this->float()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Revert back to allow NULL and default value 10 (original state)
        $this->alterColumn('driver', 'point', $this->float()->defaultValue(10));
    }
}
