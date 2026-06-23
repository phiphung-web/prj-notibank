<?php

use yii\db\Migration;

/**
 * Handles adding reason column to table `{{%message_zns}}`.
 */
class m251030_034616_add_reason_column_to_message_zns_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%message_zns}}', 'reason', $this->text()->after('message'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%message_zns}}', 'reason');
    }
}
