<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%agency}}`.
 */
class m230511_090726_create_agency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%agency}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'address' => $this-> string(255),
            'phone' => $this->integer(),
            'email' => $this->string(255),
            'contact_person' => $this->string(255),
            'note' => $this->string(),
            'status' => $this->tinyInteger(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%agency}}');
    }
}
