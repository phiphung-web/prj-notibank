<?php

use yii\db\Migration;

/**
 * Class m240909_031634_add_deleted_at_to_driver_table
 */
class m240909_031634_add_deleted_at_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%driver}}', 'deleted_at', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240909_031634_add_deleted_at_to_driver_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240909_031634_add_deleted_at_to_driver_table cannot be reverted.\n";

        return false;
    }
    */
}
