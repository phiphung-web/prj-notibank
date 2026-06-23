<?php

use yii\db\Migration;

/**
 * Class m230727_042140_add_field_userid_created_and_userid_updated_to_trip_table
 */
class m230727_042140_add_field_userid_created_and_userid_updated_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'userid_created', $this->integer());
        $this->addColumn('trip', 'userid_updated', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'userid_created');
        $this->dropColumn('trip', 'userid_updated');

        return false;
    }
}
