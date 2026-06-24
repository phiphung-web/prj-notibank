<?php

use yii\db\Migration;

/**
 * Class m231218_172252_add_field_note_to_car
 */
class m231218_172252_add_field_note_to_car extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('car', 'note', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('car', 'album_insurance');

        return false;
    }
}
