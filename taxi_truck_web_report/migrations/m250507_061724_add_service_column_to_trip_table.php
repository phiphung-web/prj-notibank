<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%trip}}`.
 */
class m250507_061724_add_service_column_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%trip}}', 'service', $this->text()->null()->comment('Các dịch vụ đã chọn (JSON hoặc chuỗi phân tách)'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%trip}}', 'service');
    }
}
