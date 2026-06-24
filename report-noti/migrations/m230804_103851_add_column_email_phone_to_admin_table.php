<?php

use yii\db\Migration;

/**
 * Class m230804_103851_add_column_email_phone_to_admin_table
 */
class m230804_103851_add_column_email_phone_to_admin_table extends Migration
{
    /**
     * {@inheritdoc}
     */

    public function up()
    {
        $this->addColumn('admin', 'email', $this->string());
        $this->addColumn('admin', 'phone', $this->string(20));
    }

    public function down()
    {
        $this->dropColumn('admin', 'email');
        $this->dropColumn('admin', 'phone');
    }
}
