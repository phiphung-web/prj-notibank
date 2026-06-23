<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%token}}`.
 */
class m230620_064541_create_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%token}}', [
            'id' => $this->primaryKey(),
            'driver_id' => $this->bigInteger(),
            'access_token' => $this->text(),
            'refresh_token' => $this->text(),
            'expiration_date' => $this->datetime(),
        ]);
        $this->addForeignKey('fk-token-driver_id', '{{%token}}', 'driver_id', '{{%driver}}', 'id', 'CASCADE', 'CASCADE');
        $this->addColumn('driver', 'register', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-token-driver_id', '{{%token}}');
        $this->dropTable('{{%token}}');
        $this->dropColumn('driver', 'register');
    }
}
