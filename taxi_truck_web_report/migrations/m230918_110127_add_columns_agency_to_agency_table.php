<?php

use yii\db\Migration;

/**
 * Class m230918_110127_add_columns_agency_to_agency_table
 */
class m230918_110127_add_columns_agency_to_agency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('agency', 'parent_id', $this->integer()->defaultValue(null)->after('id'));
        $this->addColumn('agency', 'token', $this->text()->defaultValue(null)->after('note'));
        $this->addColumn('agency', 'qr_code', $this->text()->defaultValue(null)->after('token'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('agency', 'parent_id');
        $this->dropColumn('agency', 'token');
        $this->dropColumn('agency', 'qr_code');

        return false;
    }
}
