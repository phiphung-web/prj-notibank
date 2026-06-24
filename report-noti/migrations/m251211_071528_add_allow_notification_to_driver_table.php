<?php

use yii\db\Migration;

class m251211_071528_add_allow_notification_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%driver}}', 'allow_notification', $this->tinyInteger(1)->notNull()->defaultValue(1)->after('status'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%driver}}', 'allow_notification');
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251211_071528_add_allow_notification_to_driver_table cannot be reverted.\n";

        return false;
    }
    */
}
