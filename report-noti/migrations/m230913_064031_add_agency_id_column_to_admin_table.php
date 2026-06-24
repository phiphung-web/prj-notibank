<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%admin}}`.
 */
class m230913_064031_add_agency_id_column_to_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('admin', 'agency_id', $this->bigInteger()->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('admin', 'agency_id');
    }
}
