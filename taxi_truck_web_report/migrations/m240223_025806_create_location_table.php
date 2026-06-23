<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%location}}`.
 */
class m240223_025806_create_location_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%location}}', [
            'id' => $this->primaryKey(),
            'place_id' => $this->integer()->defaultValue(0),
            'latitude' => $this->double()->notNull(),
            'longitude' => $this->double()->notNull(),
            'display_name' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%location}}');
    }
}
