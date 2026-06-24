<?php

use yii\db\Migration;

/**
 * Class m230726_040607_add_field_address_to_area_relationship
 */
class m230726_040607_add_field_address_to_area_relationship extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('area_relationship', 'address', $this->text()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('area_relationship', 'address');

        return false;
    }
}
