<?php

use yii\db\Migration;

/**
 * Class m251029_231009_add_is_sub_driver_to_driver_table
 */
class m251029_231009_add_is_sub_driver_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'is_sub_driver', $this->tinyInteger()->defaultValue(0)->comment('0: Driver chính | 1: Driver phụ'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'is_sub_driver');
    }
}
