<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%area}}`.
 */
class m230720_035904_create_area_table extends Migration
{
    public function up()
    {
        $this->createTable('area', [
            'id' => $this->primaryKey(),
            'provinceid' => $this->string(20)->notNull(),
            'districtid' => $this->string(20)->notNull(),
            'street' => $this->text(),
            'area_name' => $this->text(),
            'description' => $this->text(),
            'price_list' => $this->text(),
            'created_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_on' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    public function down()
    {
        $this->dropTable('area');
    }
}
