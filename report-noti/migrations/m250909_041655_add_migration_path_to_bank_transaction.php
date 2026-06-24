<?php

use yii\db\Migration;

class m250909_041655_add_migration_path_to_bank_transaction extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bank_transaction}}', 'qrcode_path', $this->string(255)->null()->after('account_number'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bank_transaction}}', 'qrcode_path');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250909_041655_add_migration_path_to_bank_transaction cannot be reverted.\n";

        return false;
    }
    */
}
