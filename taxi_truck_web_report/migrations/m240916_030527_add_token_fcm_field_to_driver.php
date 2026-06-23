<?php

use yii\db\Migration;

/**
 * Class m240916_030527_add_token_fcm_field_to_driver
 */
class m240916_030527_add_token_fcm_field_to_driver extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'token_fcm', $this->text()->comment('FCM key'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240916_030527_add_token_fcm_field_to_driver cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240916_030527_add_token_fcm_field_to_driver cannot be reverted.\n";

        return false;
    }
    */
}
