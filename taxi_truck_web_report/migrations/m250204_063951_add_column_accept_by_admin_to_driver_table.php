<?php

use yii\db\Migration;

/**
 * Class m250204_063951_add_column_accept_by_admin_to_driver_table
 */
class m250204_063951_add_column_accept_by_admin_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'admin_id_accepted', $this->integer());
        $this->addColumn('driver', 'accepted_on', $this->dateTime()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'admin_id_accepted');
        $this->dropColumn('driver', 'accepted_on');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250204_063951_add_column_accept_by_admin_to_driver_table cannot be reverted.\n";

        return false;
    }
    */
}
