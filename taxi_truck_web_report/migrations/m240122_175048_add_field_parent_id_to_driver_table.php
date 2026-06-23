<?php

use yii\db\Migration;

/**
 * Class m240122_175048_add_field_parent_id_to_driver_table
 */
class m240122_175048_add_field_parent_id_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'parent_id', $this->bigInteger()->defaultValue(0)->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'parent_id');

        return false;
    }
}
