<?php

use yii\db\Migration;

class m251015_103617_fix_parent_id_default_to_0 extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('driver', ['parent_id' => 0], ['parent_id' => null]);
        $this->alterColumn('driver', 'parent_id', $this->bigInteger()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('driver', 'parent_id', $this->bigInteger()->defaultValue(0));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251015_103617_fix_parent_id_default_to_0 cannot be reverted.\n";

        return false;
    }
    */
}
