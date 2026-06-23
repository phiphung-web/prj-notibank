<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%config_auto_sale}}`.
 */
class m240312_035306_create_config_auto_sale_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%config_auto_sale}}', [
            'id' => $this->primaryKey(),
            'type_of_car' => $this->integer(),
            'minute_before' => $this->integer()->defaultValue(0),
            'schedule' => $this->integer()->defaultValue(0),
            'price' => $this->integer()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%config_auto_sale}}');
    }
}
