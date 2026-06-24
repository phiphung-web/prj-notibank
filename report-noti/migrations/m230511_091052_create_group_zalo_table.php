<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_zalo}}`.
 */
class m230511_091052_create_group_zalo_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_zalo}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%group_zalo}}');
    }
}
