<?php

use yii\db\Migration;

/**
 * Class m240221_032439_add_field_is_auto_price_to_trip_table
 */
class m240221_032439_add_field_is_auto_price_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'is_auto_price', $this->boolean()->defaultValue(false)->after('buynow'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'is_auto_price');

        return false;
    }
}
