<?php

use yii\db\Migration;

/**
 * Class m240518_052627_add_expected_return_date_to_customer_service
 */
class m240518_052627_add_expected_return_date_to_customer_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%customer_service}}', 'times', $this->tinyInteger(1)->defaultValue(0)->after('point'));
        $this->addColumn('{{%trip}}', 'expected_return_date', $this->datetime()->after('pickup_time'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%customer_service}}', 'times');
        $this->dropColumn('{{%trip}}', 'expected_return_date');
    }
}
