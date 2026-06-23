<?php

use yii\db\Migration;

/**
 * Class m230811_032605_add_column_status_add_bonus_to_admin_table
 */
class m230811_032605_add_column_status_add_bonus_to_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('admin', 'status', $this->tinyInteger()->defaultValue(0));
        $this->addColumn('admin', 'bonus', $this->string()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('admin', 'status');
        $this->dropColumn('admin', 'bonus');
    }
}
