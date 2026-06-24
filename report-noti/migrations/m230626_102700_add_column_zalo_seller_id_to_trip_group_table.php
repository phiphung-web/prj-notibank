<?php

use yii\db\Migration;

/**
 * Class m230626_102700_add_column_zalo_seller_id_to_trip_group_table
 */
class m230626_102700_add_column_zalo_seller_id_to_trip_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip_group', 'zalo_seller_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip_group', 'zalo_seller_id');

        return false;
    }
}
