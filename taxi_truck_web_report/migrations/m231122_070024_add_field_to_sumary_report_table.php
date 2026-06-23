<?php

use yii\db\Migration;

/**
 * Class m231122_070024_add_field_to_sumary_report_table
 */
class m231122_070024_add_field_to_sumary_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('summary_report', 'mail_source_success', $this->integer()->defaultValue(0)->after('mail_source'));
        $this->addColumn('summary_report', 'call_source_success', $this->integer()->defaultValue(0)->after('call_source'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('summary_report', 'mail_source_success');
        $this->dropColumn('summary_report', 'call_source_success');

        return false;
    }
}
