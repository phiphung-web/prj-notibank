<?php

use yii\db\Migration;

/**
 * Class m240312_041811_add_field_source_trip_to_request_callback_table
 */
class m240312_041811_add_field_source_trip_to_request_callback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('request_call_back', 'source_trip', $this->tinyInteger()->defaultValue(0)->after('type_reject'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
