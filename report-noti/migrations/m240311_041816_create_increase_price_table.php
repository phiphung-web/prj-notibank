<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%increase_price}}`.
 */
class m240311_041816_create_increase_price_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%increase_price}}', [
            'id' => $this->primaryKey(),
            'type_of_car' => $this->integer(),
            'minute_before' => $this->integer()->defaultValue(0),
            'price_increase' => $this->integer()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%increase_price}}');
    }
}
