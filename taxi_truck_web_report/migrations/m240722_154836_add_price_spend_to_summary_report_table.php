<?php

use yii\db\Migration;

/**
 * Class m240722_154836_add_price_spend_to_summary_report_table
 */
class m240722_154836_add_price_spend_to_summary_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('summary_report', 'spend_price', $this->integer()->defaultValue(0)->after('driver_price'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }
}
