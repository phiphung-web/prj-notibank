<?php

use yii\db\Migration;

/**
 * Class m230622_074327_create_group_zalo_catalogue
 */
class m230622_074327_create_group_zalo_catalogue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_zalo_catalogue}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'status' => $this->tinyInteger(),
            'created_on' => $this->datetime()->defaultValue(null),
            'modified_on' => $this->datetime()->defaultValue(null),
        ]);
        $this->addColumn('group_zalo', 'group_zalo_catalogue', $this->integer()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%group_zalo_catalogue}}');
        $this->dropColumn('group_zalo', 'group_zalo_catalogue');
    }
}
