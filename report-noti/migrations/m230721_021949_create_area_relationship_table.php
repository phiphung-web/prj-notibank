<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%area_relationship}}`.
 */
class m230721_021949_create_area_relationship_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%area_relationship}}', [
            'id' => $this->primaryKey(),
            'area_id' => $this->integer()->notNull(),
            'districtid' => $this->string(20)->notNull(),
            'provinceid' => $this->string(20)->notNull(),
            'type_of_car' => $this->integer(),
            'street' => $this->text(),
            'time' => $this->integer(),
            'schedule' => $this->integer(),
            'price' => $this->integer(),
            'roundtrip_price' => $this->integer(),
            'description' => $this->text(),
            'created_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%area_relationship}}');
    }
}
