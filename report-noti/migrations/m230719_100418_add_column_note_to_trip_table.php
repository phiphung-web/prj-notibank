<?php

use yii\db\Migration;

/**
 * Class m230719_100418_add_column_note_to_trip_table
 */
class m230719_100418_add_column_note_to_trip_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('trip', 'note', $this->text()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('trip', 'note');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230719_100418_add_column_note_to_trip_table cannot be reverted.\n";

        return false;
    }
    */
}
