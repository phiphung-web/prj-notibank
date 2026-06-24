<?php

use yii\db\Migration;

/**
 * Class m230923_044728_add_field_count_to_booking_table
 */
class m230923_044728_add_field_count_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'count', $this->integer()->after('type_of_car'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('booking', 'count');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230923_044728_add_field_count_to_booking_table cannot be reverted.\n";

        return false;
    }
    */
}
