<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%driver}}`.
 */
class m240215_071111_add_location_columnto_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'location', $this->text()->after('longitude'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'location');

        return false;
    }
}
