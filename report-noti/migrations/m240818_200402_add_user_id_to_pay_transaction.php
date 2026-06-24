<?php

use yii\db\Migration;

/**
 * Class m240818_200402_add_user_id_to_pay_transaction
 */
class m240818_200402_add_user_id_to_pay_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%pay_transaction}}', 'user_id', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240818_200402_add_user_id_to_pay_transaction cannot be reverted.\n";
        $this->addColumn('{{%pay_transaction}}', 'user_id', $this->integer()->defaultValue(0));

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240818_200402_add_user_id_to_pay_transaction cannot be reverted.\n";

        return false;
    }
    */
}
