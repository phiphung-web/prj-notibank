<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%config_auto_sale}}`.
 */
class m240315_094306_add_to_minute_column_to_config_auto_sale_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('config_auto_sale', 'minute_before', 'from_minute');
        $this->addColumn('config_auto_sale', 'to_minute', $this->integer()->after('from_minute')->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('config_auto_sale', 'from_minute', 'minute_before');
        $this->dropColumn('config_auto_sale', 'to_minute');
    }
}
