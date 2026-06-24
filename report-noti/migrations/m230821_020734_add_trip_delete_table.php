<?php

use yii\db\Migration;

/**
 * Class m230821_020734_add_trip_delete_table
 */
class m230821_020734_add_trip_delete_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('trip_delete', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'created_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'note' => $this->text(),
            'data_trip' => $this->text(),
            'is_rollback' => $this->tinyInteger()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('trip_delete');
    }
}
