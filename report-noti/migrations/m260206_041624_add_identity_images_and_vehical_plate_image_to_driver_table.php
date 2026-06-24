<?php

use yii\db\Migration;

class m260206_041624_add_identity_images_and_vehical_plate_image_to_driver_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $carTable = $this->db->getTableSchema('{{%car}}');
        if (
            !isset($carTable->columns['vehicle_plate_image'])
            && isset($carTable->columns['vehical_plate_image']) === false
        ) {
            $this->addColumn(
                '{{%car}}',
                'vehicle_plate_image',
                $this->string(255)->null()->after('registration_certificate_behind')
            );
        }

        $driverTable = $this->db->getTableSchema('{{%driver}}');

        if (!isset($driverTable->columns['identity_front_image'])) {
            $this->addColumn(
                '{{%driver}}',
                'identity_front_image',
                $this->string(255)->null()->after('driver_license_behind')
            );
        }

        if (!isset($driverTable->columns['identity_back_image'])) {
            $this->addColumn(
                '{{%driver}}',
                'identity_back_image',
                $this->string(255)->null()->after('identity_front_image')
            );
        }
    }

    public function safeDown()
    {
        $carTable = $this->db->getTableSchema('{{%car}}');
        if (isset($carTable->columns['vehicle_plate_image'])) {
            $this->dropColumn('{{%car}}', 'vehicle_plate_image');
        }

        $driverTable = $this->db->getTableSchema('{{%driver}}');
        if (isset($driverTable->columns['identity_back_image'])) {
            $this->dropColumn('{{%driver}}', 'identity_back_image');
        }
        if (isset($driverTable->columns['identity_front_image'])) {
            $this->dropColumn('{{%driver}}', 'identity_front_image');
        }
    }
}
