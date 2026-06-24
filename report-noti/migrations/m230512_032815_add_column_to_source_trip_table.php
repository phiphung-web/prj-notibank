<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%source_trip}}`.
 */
class m230512_032815_add_column_to_source_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('source_trip', 'agency_id', $this->integer());
        $this->addColumn('source_trip', 'zalo_id', $this->integer());
        $this->addColumn('source_trip', 'facebook', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('source_trip', 'agency_id');
        $this->dropColumn('source_trip', 'zalo_id');
        $this->dropColumn('source_trip', 'facebook');
    }
}
