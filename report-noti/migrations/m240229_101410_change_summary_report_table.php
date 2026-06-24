<?php

use yii\db\Migration;

/**
 * Class m240229_101410_change_summary_report_table
 */
class m240229_101410_change_summary_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('summary_report', 'source_trip', $this->text());
        $this->dropColumn('summary_report', 'mail_source');
        $this->dropColumn('summary_report', 'call_source');
        $this->dropColumn('summary_report', 'comeback_source');
        $this->dropColumn('summary_report', 'agency_source');
        $this->dropColumn('summary_report', 'zalo_oa_source');
        $this->dropColumn('summary_report', 'mail_source_success');
        $this->dropColumn('summary_report', 'call_source_success');
        $this->dropColumn('summary_report', 'call_back');
        $this->dropColumn('summary_report', 'call_back_success');
        $this->dropColumn('summary_report', 'facebook_source');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240229_101410_change_summary_report_table cannot be reverted.\n";

        return false;
    }
}
