<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%trip_group}}`.
 */
class m230522_064311_create_trip_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%trip_group}}', [
            'id' => $this->bigPrimaryKey(),
            'group_zalo_id' => $this->bigInteger(),
            'type' => $this->tinyInteger(),
            'point' => $this->float(),
            'driver_name' => $this->string(255),
            'driver_phone' => $this->string(255),
            'price' => $this->integer(),
            'created_on' => $this->datetime()->defaultValue(null),
            'modified_on' => $this->datetime()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%trip_group}}');
    }
}
