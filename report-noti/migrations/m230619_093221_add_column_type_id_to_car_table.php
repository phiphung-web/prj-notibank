<?php

use yii\db\Migration;

/**
 * Class m230619_093221_add_column_type_id_to_car_table
 */
class m230619_093221_add_column_type_id_to_car_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('car', 'type_of_car', $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('car', 'type_of_car');

        return false;
    }
}
