<?php

use yii\db\Migration;

/**
 * Class m231015_183327_add_field_price_to_agency_table
 */
class m231015_183327_add_field_price_to_agency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('agency', 'price', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('agency', 'price');

        return false;
    }
}
