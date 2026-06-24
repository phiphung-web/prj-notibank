<?php

use yii\db\Migration;

/**
 * Class m250301_045327_update_calculation_formula_structur
 */
class m250301_045327_update_calculation_formula_structur extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Đổi tên cột price_closer_than_km thành price
        $this->renameColumn('calculation_formula', 'price_closer_than_km', 'price');

        // Đổi tên cột price_over_km thành km_end
        $this->renameColumn('calculation_formula', 'price_over_km', 'km_end');

        // Đổi tên cột km thành km_start
        $this->renameColumn('calculation_formula', 'km', 'km_start');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('calculation_formula', 'price', 'price_closer_than_km');
        $this->renameColumn('calculation_formula', 'km_end', 'price_over_km');
        $this->renameColumn('calculation_formula', 'km_start', 'km');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250301_045327_update_calculation_formula_structur cannot be reverted.\n";

        return false;
    }
    */
}
