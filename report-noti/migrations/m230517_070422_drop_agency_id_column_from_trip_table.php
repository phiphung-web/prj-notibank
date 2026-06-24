<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%trip}}`.
 */
class m230517_070422_drop_agency_id_column_from_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('trip', 'agency_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('trip', 'agency_id', $this->integer());
    }
}
