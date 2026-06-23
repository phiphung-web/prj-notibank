<?php

use yii\db\Migration;

/**
 * Class m241214_081128_add_is_display_to_bank_transaction
 */
class m241214_081128_add_is_display_to_bank_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bank_transaction}}', 'is_display', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241214_081128_add_is_display_to_bank_transaction cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241214_081128_add_is_display_to_bank_transaction cannot be reverted.\n";

        return false;
    }
    */
}
