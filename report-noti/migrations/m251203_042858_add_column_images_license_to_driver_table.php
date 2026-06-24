<?php

use yii\db\Migration;

class m251203_042858_add_column_images_license_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'driver_license_front', $this->text()->after('english'));
        $this->addColumn('driver', 'driver_license_behind', $this->text()->after('driver_license_front'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'driver_license_front');
        $this->dropColumn('driver', 'driver_license_behind');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251203_042858_add_column_images_license_to_driver_table cannot be reverted.\n";

        return false;
    }
    */
}
