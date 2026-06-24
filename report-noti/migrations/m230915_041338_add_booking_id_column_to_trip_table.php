<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip}}`.
 */
class m230915_041338_add_booking_id_column_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'booking_id', $this->bigInteger()->notNull()->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'booking_id');
    }
}
