<?php

use yii\db\Migration;

/**
 * Class m231109_145622_add_field_to_agency_table
 */
class m231109_145622_add_field_to_agency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('agency', 'percent', $this->float()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('agency', 'percent');

        return false;
    }
}
