<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%area_configuration}}`.
 */
class m230719_082229_create_area_configuration_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%area_configuration}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(255),
            'value' => $this->text(),
            'created_on' => $this->datetime()->defaultValue(null),
            'modified_on' => $this->datetime()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%area_configuration}}');
    }
}
