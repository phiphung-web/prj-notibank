<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%calculation_formula}}`.
 */
class m240219_062340_create_calculation_formula_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%calculation_formula}}', [
            'id' => $this->primaryKey(),
            'type_of_car' => $this->integer(),
            'time_start' => $this->time(),
            'time_end' => $this->time(),
            'schedule' => $this->integer()->defaultValue(0),
            'price_closer_than_km' => $this->integer()->defaultValue(0),
            'price_over_km' => $this->integer()->defaultValue(0),
            'km' => $this->integer()->defaultValue(0),
            'surcharge' => $this->integer()->defaultValue(0),
            'price_wait' => $this->integer()->defaultValue(0),
            'description' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%calculation_formula}}');
    }
}
