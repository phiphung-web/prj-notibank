<?php

use yii\db\Migration;

/**
 * Class m231113_165958_add_field_google_excel_to_booking_table
 */
class m231113_165958_add_field_google_excel_to_booking_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('booking', 'website', $this->string(255));
        $this->addColumn('booking', 'google_excel', $this->tinyInteger(1)->defaultValue(0));
        $this->addColumn('agency', 'website', $this->string(255));
        $this->addColumn('agency', 'google_excel', $this->tinyInteger(1)->defaultValue(0));
        $this->addColumn('request_call_back', 'website', $this->string(255));
        $this->addColumn('request_call_back', 'google_excel', $this->tinyInteger(1)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('booking', 'website');
        $this->dropColumn('booking', 'google_excel');
        $this->dropColumn('agency', 'website');
        $this->dropColumn('agency', 'google_excel');
        $this->dropColumn('request_call_back', 'website');
        $this->dropColumn('request_call_back', 'google_excel');

        return false;
    }
}
