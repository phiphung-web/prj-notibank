<?php

use yii\db\Migration;

/**
 * Class m231029_171441_add_field_to_request_callback_table
 */
class m231029_171441_add_field_to_request_callback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('request_call_back', 'utm_source', $this->text());
        $this->addColumn('request_call_back', 'utm_campaign', $this->text());
        $this->addColumn('request_call_back', 'utm_medium', $this->text());
        $this->addColumn('request_call_back', 'remote_ip', $this->text());
        $this->addColumn('request_call_back', 'url', $this->text());
        $this->addColumn('request_call_back', 'tracking_info', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('request_call_back', 'utm_source');
        $this->dropColumn('request_call_back', 'utm_campaign');
        $this->dropColumn('request_call_back', 'utm_medium');
        $this->dropColumn('request_call_back', 'remote_ip');
        $this->dropColumn('request_call_back', 'url');
        $this->dropColumn('request_call_back', 'tracking_info');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231029_171441_add_field_to_request_callback_table cannot be reverted.\n";

        return false;
    }
    */
}
