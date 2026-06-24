<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip}}`.
 */
class m251006_092816_add_bonus_and_toll_fee_columns_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%trip}}', 'bonus', $this->integer()->defaultValue(0)->after('price_customer'));
        $this->addColumn('{{%trip}}', 'is_toll_fee', $this->boolean()->defaultValue(false)->after('bonus'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%trip}}', 'is_toll_fee');
        $this->dropColumn('{{%trip}}', 'bonus');
    }
}
