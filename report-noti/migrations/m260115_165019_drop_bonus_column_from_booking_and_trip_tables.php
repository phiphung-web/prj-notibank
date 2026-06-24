<?php

use yii\db\Migration;

/**
 * Handles dropping the bonus column from table `{{%booking}}` and `{{%trip}}`.
 */
class m260115_165019_drop_bonus_column_from_booking_and_trip_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%booking}}', 'bonus');
        $this->dropColumn('{{%trip}}', 'bonus');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%booking}}', 'bonus', $this->integer()->defaultValue(0)->after('service'));
        $this->addColumn('{{%trip}}', 'bonus', $this->integer()->defaultValue(0)->after('price_customer'));
    }
}
