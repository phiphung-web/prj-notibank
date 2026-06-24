<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%system_configuration}}`.
 */
class m230601_025257_create_system_configuration_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%system_configuration}}', [
            'id' => $this->primaryKey(),
            'keyword' => $this->string(255),
            'content' => $this->text(),
            'created_on' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_configuration}}');
    }
}
