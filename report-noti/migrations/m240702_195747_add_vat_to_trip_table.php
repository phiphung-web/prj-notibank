<?php

use yii\db\Migration;

/**
 * Class m240702_195747_add_vat_to_trip_table
 */
class m240702_195747_add_vat_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'price_vat', $this->integer()->defaultValue(0)->after('price_customer'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240702_195747_add_vat_to_trip_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240702_195747_add_vat_to_trip_table cannot be reverted.\n";

        return false;
    }
    */
}
