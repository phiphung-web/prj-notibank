<?php

use yii\db\Migration;

/**
 * Class m230531_030922_add_column_driver_ban_to_driver_table
 */
class m230531_030922_add_column_driver_ban_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'driver_ban', $this->tinyInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'driver_ban');

        return false;
    }
}
