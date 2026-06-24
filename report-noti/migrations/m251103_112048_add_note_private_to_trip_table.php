<?php

use yii\db\Migration;

class m251103_112048_add_note_private_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%trip}}', 'note_private', $this->text()->null()->after('description'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%trip}}', 'note_private');
    }

    /*
     * // Use up()/down() to run migration code without a transaction.
     * public function up()
     * {
     *
     * }
     *
     * public function down()
     * {
     *     echo "m251103_112048_add_note_private_to_trip_table cannot be reverted.\n";
     *
     *     return false;
     * }
     */
}
