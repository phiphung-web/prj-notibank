<?php

use yii\db\Migration;

/**
 * Class m250522_072948_add_overnight_fee_to_calculation_formula_table
 */
class m250522_072948_add_overnight_fee_to_calculation_formula_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%calculation_formula}}',
            'overnight_fee',
            $this->integer()->defaultValue(0)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%calculation_formula}}', 'overnight_fee');
    }
}
