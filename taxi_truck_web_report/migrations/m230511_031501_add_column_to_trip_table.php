<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip}}`.
 */
class m230511_031501_add_column_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addColumn('trip', 'type_of_car', $this->string());
        $this->addColumn('trip', 'is_agency', $this->boolean());
        $this->addColumn('trip', 'is_have_bill', $this->boolean());
        $this->addColumn('trip', 'is_collect_money', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropColumn('trip', 'type_of_car');
        $this->dropColumn('trip', 'is_agency');
        $this->dropColumn('trip', 'is_have_bill');
        $this->dropColumn('trip', 'is_collect_money');
    }
}
