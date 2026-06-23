<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%pay_transaction}}`.
 */
class m230831_170227_add_columns_to_pay_transaction_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%pay_transaction}}', 'id_pay_transaction', $this->string(30));
        $this->addColumn('{{%pay_transaction}}', 'phone', $this->string(255));
        $this->addColumn('{{%pay_transaction}}', 'content_bank', $this->text());
        $this->addColumn('{{%pay_transaction}}', 'type', $this->tinyInteger()->defaultValue(0));
        $this->addColumn('{{%pay_transaction}}', 'status', $this->tinyInteger()->defaultValue(0));
        $this->addColumn('{{%pay_transaction}}', 'type_bank', $this->string(255));
        $this->addColumn('{{%pay_transaction}}', 'admin_id_accepted', $this->integer());
        $this->addColumn('{{%pay_transaction}}', 'accepted_at', $this->datetime());
    }

    public function down()
    {
        $this->dropColumn('{{%pay_transaction}}', 'id_pay_transaction');
        $this->dropColumn('{{%pay_transaction}}', 'phone');
        $this->dropColumn('{{%pay_transaction}}', 'content_bank');
        $this->dropColumn('{{%pay_transaction}}', 'type');
        $this->dropColumn('{{%pay_transaction}}', 'type_bank');
        $this->dropColumn('{{%pay_transaction}}', 'status');
        $this->dropColumn('{{%pay_transaction}}', 'admin_id_accepted');
        $this->dropColumn('{{%pay_transaction}}', 'accepted_at');
    }
}
