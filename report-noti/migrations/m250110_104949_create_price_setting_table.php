<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%price_setting}}`.
 */
class m250110_104949_create_price_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%price_setting}}', [
            'id' => $this->primaryKey(),
            'agency_id' => $this->integer()->notNull()->null(),
            'price' => $this->integer()->defaultValue(0),
            'percent' => $this->decimal(5, 2)->defaultValue(1),
            'start_date' => $this->dateTime()->null(),
            'end_date' => $this->dateTime()->null(),
            'active' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultValue(null)->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk-price_setting-agency_id', // Tên khóa phụ
            '{{%price_setting}}',        // Bảng hiện tại
            'agency_id',                 // Cột trong bảng hiện tại
            '{{%agency}}',               // Bảng được tham chiếu
            'id',                        // Cột trong bảng được tham chiếu
            'CASCADE',                   // Xóa cascade
            'CASCADE'                    // Cập nhật cascade
        );

        $this->insert('{{%price_setting}}', [
            'id' => 1,
            'agency_id' => null,
            'price' => 0,
            'percent' => 1,
            'start_date' => null,
            'end_date' => null,
            'active' => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-price_setting-agency_id',
            '{{%price_setting}}'
        );
        $this->delete('{{%price_setting}}', ['id' => 1]);
        $this->dropTable('{{%price_setting}}');
    }
}
