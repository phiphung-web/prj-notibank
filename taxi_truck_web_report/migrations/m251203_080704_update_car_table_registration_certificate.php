<?php

use yii\db\Migration;

class m251203_080704_update_car_table_registration_certificate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('car', 'car_in');
        $this->dropColumn('car', 'car_out');
        $this->dropColumn('car', 'car_front');
        $this->dropColumn('car', 'car_behind');


        $this->addColumn('car', 'registration_certificate_front', $this->text()->null()->after('album_insurance'));
        $this->addColumn('car', 'registration_certificate_behind', $this->text()->null()->after('registration_certificate_front'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->addColumn('car', 'car_in', $this->text()->null());
        $this->addColumn('car', 'car_out', $this->text()->null());
        $this->addColumn('car', 'car_front', $this->text()->null());
        $this->addColumn('car', 'car_behind', $this->text()->null());


        $this->dropColumn('car', 'registration_certificate_front');
        $this->dropColumn('car', 'registration_certificate_behind');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251203_080704_update_car_table_registration_certificate cannot be reverted.\n";

        return false;
    }
    */
}
