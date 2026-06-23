<?php

use yii\db\Migration;

/**
 * Class m230915_154505_add_field_type_to_booking
 */
class m230915_154505_add_field_type_to_booking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'type', $this->tinyInteger()->defaultValue(0)->comment('0: Lich qua Mail | 1: Lịch Call | 2: Lịch đại lý'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
