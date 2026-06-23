<?php

use yii\db\Migration;

/**
 * Class m230824_104653_add_column_type_reject_to_table_booking
 */
class m230824_104653_add_column_type_reject_to_table_booking extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'type_reject', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('booking', 'type_reject');
    }
}
