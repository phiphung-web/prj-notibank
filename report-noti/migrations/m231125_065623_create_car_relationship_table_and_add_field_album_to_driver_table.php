<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%car_relationship_table_and_add_field_album_to_driver}}`.
 */
class m231125_065623_create_car_relationship_table_and_add_field_album_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'folder_image', $this->text());
        $this->addColumn('driver', 'album', $this->text());
        $this->addColumn('car', 'album', $this->text());

        $this->createTable('{{%car_relationship}}', [
            'id' => $this->primaryKey(),
            'car_id' => $this->bigInteger()->defaultValue(0),
            'driver_id' => $this->bigInteger()->defaultValue(0),
        ]);

        // Thêm khóa ngoại từ car_relationship đến car
        $this->addForeignKey(
            'fk-car_relationship-car_id',
            '{{%car_relationship}}',
            'car_id',
            '{{%car}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Thêm khóa ngoại từ car_relationship đến driver
        $this->addForeignKey(
            'fk-car_relationship-driver_id',
            '{{%car_relationship}}',
            'driver_id',
            '{{%driver}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('driver', 'folder_image');
        $this->dropColumn('driver', 'album');
        $this->dropColumn('car', 'album');
        $this->dropForeignKey('fk-car_relationship-car_id', '{{%car_relationship}}');
        $this->dropForeignKey('fk-car_relationship-driver_id', '{{%car_relationship}}');
        $this->dropTable('{{%car_relationship}}');
    }
}
