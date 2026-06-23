<?php

use yii\db\Migration;

/**
 * Class m231105_183731_add_field_presenter_to_agency__table
 */
class m231105_183731_add_field_presenter_to_agency__table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('agency', 'utm_source', $this->text());
        $this->addColumn('agency', 'utm_campaign', $this->text());
        $this->addColumn('agency', 'utm_medium', $this->text());
        $this->addColumn('agency', 'remote_ip', $this->text());
        $this->addColumn('agency', 'url', $this->text());
        $this->addColumn('agency', 'tracking_info', $this->text());
        $this->addColumn('agency', 'presenter', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('agency', 'utm_source');
        $this->dropColumn('agency', 'utm_campaign');
        $this->dropColumn('agency', 'utm_medium');
        $this->dropColumn('agency', 'remote_ip');
        $this->dropColumn('agency', 'url');
        $this->dropColumn('agency', 'tracking_info');
        $this->dropColumn('agency', 'presenter');

        return false;
    }
}
