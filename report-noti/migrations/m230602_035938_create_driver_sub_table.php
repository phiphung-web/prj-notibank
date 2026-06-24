<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%driver_sub}}`.
 */
class m230602_035938_create_driver_sub_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%driver_sub}}', [
            'id' => $this->primaryKey(),
            'trip_id' => $this->bigInteger(),
            'driver_id' => $this->bigInteger(),
            'name' => $this->string(255),
            'phone' => $this->string(255),
            'bks' => $this->string(255),
            'type' => $this->string(255),
        ]);

        $this->addColumn('trip', 'driver_sub', $this->tinyInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%driver_sub}}');
        $this->dropColumn('trip', 'driver_sub');
    }
}
