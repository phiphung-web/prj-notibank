<?php

use yii\db\Migration;

/**
 * Class m250320_140343_add_price_by_km_to_calculation_formula
 */
class m250320_140343_add_price_by_km_to_calculation_formula extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('calculation_formula', 'price_by_km', $this->integer(11)->after('price'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('calculation_formula', 'price_by_km');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250320_140343_add_price_by_km_to_calculation_formula cannot be reverted.\n";

        return false;
    }
    */
}
