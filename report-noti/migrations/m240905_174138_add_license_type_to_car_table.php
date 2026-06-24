<?php

use yii\db\Migration;

/**
 * Class m240905_174138_add_license_type_to_car_table
 */
class m240905_174138_add_license_type_to_car_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('car', 'license_type', $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('Loại biển kiểm soát'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240905_174138_add_license_type_to_car_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240905_174138_add_license_type_to_car_table cannot be reverted.\n";

        return false;
    }
    */
}
