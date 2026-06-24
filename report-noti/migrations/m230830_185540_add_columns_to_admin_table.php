<?php

use yii\db\Migration;

/**
 * Class m230830_185540_add_columns_to_admin_table
 */
class m230830_185540_add_columns_to_admin_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%admin}}', 'auth_key', $this->string(32));
        $this->addColumn('{{%admin}}', 'password_reset_token', $this->text());
        $this->addColumn('{{%admin}}', 'account_activation_token', $this->text());
        $this->addColumn('{{%admin}}', 'created_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'));
        $this->addColumn('{{%admin}}', 'updated_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
    }

    public function down()
    {
        $this->dropColumn('{{%admin}}', 'auth_key');
        $this->dropColumn('{{%admin}}', 'password_reset_token');
        $this->dropColumn('{{%admin}}', 'account_activation_token');
        $this->dropColumn('{{%admin}}', 'created_at');
        $this->dropColumn('{{%admin}}', 'updated_at');
    }
}
