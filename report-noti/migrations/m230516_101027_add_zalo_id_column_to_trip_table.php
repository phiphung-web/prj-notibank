<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip}}`.
 */
class m230516_101027_add_zalo_id_column_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'zalo_id', $this->integer());
        $this->renameColumn('trip', 'is_agency', 'agency_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'zalo_id');
        $this->alterColumn('table_name', 'is_agency', 'is_agency');
    }
}
