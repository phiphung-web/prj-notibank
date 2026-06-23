<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip_zalo_agency}}`.
 */
class m230519_034921_add_column_to_trip_zalo_agency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('group_zalo', 'price_per_point', $this->float());
        $this->addColumn('group_zalo', 'note', $this->string());
        $this->addColumn('group_zalo', 'status', $this->tinyInteger());
        $this->addColumn('agency', 'address', $this->string(255));
        $this->addColumn('agency', 'phone', $this->string(255));
        $this->addColumn('agency', 'email', $this->string(255));
        $this->addColumn('agency', 'contact_person', $this->string(255));
        $this->addColumn('agency', 'note', $this->string());
        $this->addColumn('agency', 'status', $this->tinyInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'zalo_point');
        $this->dropColumn('agency', 'price_per_point');
        $this->dropColumn('group_zalo', 'address');
        $this->dropColumn('group_zalo', 'phone');
        $this->dropColumn('group_zalo', 'email');
        $this->dropColumn('group_zalo', 'contact_person');
        $this->dropColumn('group_zalo', 'note');
        $this->dropColumn('group_zalo', 'status');
    }
}
