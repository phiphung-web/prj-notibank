<?php

use yii\db\Migration;

/**
 * Class m230626_025621_add_group_zalo_seller_table
 */
class m230626_025621_add_group_zalo_seller_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_zalo_seller}}', [
            'id' => $this->primaryKey(),
            'group_zalo_catalogue_id' => $this->text(),
            'name' => $this->string(255),
            'created_on' => $this->datetime()->defaultValue(null),
            'modified_on' => $this->datetime()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%group_zalo_seller}}');

        return false;
    }
}
