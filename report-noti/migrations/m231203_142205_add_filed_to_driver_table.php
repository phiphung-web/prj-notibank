<?php

use yii\db\Migration;

/**
 * Class m231203_142205_add_filed_to_driver_table
 */
class m231203_142205_add_filed_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'reason', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'reason');

        return false;
    }
}
