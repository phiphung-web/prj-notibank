<?php

use yii\db\Migration;

/**
 * Class m230705_024803_remove_data_from_type_of_car_table
 */
class m230705_024803_remove_data_from_type_of_car_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('type_of_car', ['name' => ['Xe 5+2 chỗ', 'Xe 45 chỗ', 'Xe 54 chỗ', 'Xe loại khác']]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
