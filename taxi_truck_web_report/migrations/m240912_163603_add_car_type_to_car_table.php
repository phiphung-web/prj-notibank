<?php

use yii\db\Migration;

/**
 * Class m240912_163603_add_car_type_to_car_table
 */
class m240912_163603_add_car_type_to_car_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('car', 'car_type', $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('Loại xe (xăng/điện)'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240912_163603_add_car_type_to_car_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240912_163603_add_car_type_to_car_table cannot be reverted.\n";

        return false;
    }
    */
}
