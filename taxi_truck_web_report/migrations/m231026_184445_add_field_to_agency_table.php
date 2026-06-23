<?php

use yii\db\Migration;

/**
 * Class m231026_184445_add_field_to_agency_table
 */
class m231026_184445_add_field_to_agency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('agency', 'send_price', $this->tinyInteger(1)->defaultValue(1));
        $this->addColumn('agency', 'agency_debt', $this->tinyInteger(1)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('agency', 'send_price');
        $this->dropColumn('agency', 'agency_debt');

        return false;
    }
}
