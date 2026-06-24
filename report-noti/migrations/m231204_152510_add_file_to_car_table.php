<?php

use yii\db\Migration;

/**
 * Class m231204_152510_add_file_to_car_table
 */
class m231204_152510_add_file_to_car_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('car', 'name', $this->text());
        $this->addColumn('car', 'phone', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('car', 'phone');
        $this->dropColumn('car', 'name');

        return false;
    }
}
