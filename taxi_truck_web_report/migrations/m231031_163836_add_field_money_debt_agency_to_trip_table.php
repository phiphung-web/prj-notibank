<?php

use yii\db\Migration;

/**
 * Class m231031_163836_add_field_money_debt_agency_to_trip_table
 */
class m231031_163836_add_field_money_debt_agency_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'money_debt_agency', $this->integer()->defaultValue(0)->after('agency_debt'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'money_debt_agency');

        return false;
    }
}
