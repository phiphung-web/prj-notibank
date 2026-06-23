<?php

use yii\db\Migration;

/**
 * Class m231015_155335_add_field_agency_debt_to_trip_table
 */
class m231015_155335_add_field_agency_debt_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'agency_debt', $this->tinyInteger()->defaultValue(0)->after('driver_debt'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'agency_debt');

        return false;
    }
}
