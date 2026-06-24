<?php

use yii\db\Migration;

/**
 * Class m240327_062158_update_column_from_config_auto_sale_table
 */
class m240327_062158_update_column_from_config_auto_sale_table extends Migration
{
    public function beforeUp()
    {
        $this->update('config_auto_sale', ['from_time' => new \yii\db\Expression('TIME_FORMAT(from_minute, "%H:%i:%s")')]);
        $this->update('config_auto_sale', ['to_time' => new \yii\db\Expression('TIME_FORMAT(to_minute, "%H:%i:%s")')]);
    }
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('config_auto_sale', 'from_minute', 'from_time');
        $this->alterColumn('config_auto_sale', 'from_time', $this->time());
        $this->renameColumn('config_auto_sale', 'to_minute', 'to_time');
        $this->alterColumn('config_auto_sale', 'to_time', $this->time());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('config_auto_sale', 'from_time', $this->integer()->defaultValue(0));
        $this->renameColumn('config_auto_sale', 'from_time', 'from_minute');
        $this->alterColumn('config_auto_sale', 'to_time', $this->integer()->defaultValue(0));
        $this->renameColumn('config_auto_sale', 'to_time', 'to_minute');
    }
}
