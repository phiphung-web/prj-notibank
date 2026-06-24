<?php

use yii\db\Migration;

/**
 * Class m240226_024732_remove_fields_from_driver_and_car
 */
class m240226_024732_remove_fields_from_driver_and_car extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('driver', 'email');
        $this->dropColumn('driver', 'birthday');
        $this->dropColumn('driver', 'address_contact');
        $this->dropColumn('driver', 'address_permanent');
        $this->dropColumn('driver', 'identity_card');
        $this->dropColumn('driver', 'identity_card_created_on');
        $this->dropColumn('driver', 'identity_card_created_at');
        $this->dropColumn('driver', 'album_id_card');
        $this->dropColumn('driver', 'album_driving_license');
        $this->dropColumn('car', 'album_vehicle_certificate');
        $this->dropColumn('car', 'album_car');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240226_024732_remove_fields_from_driver_and_car cannot be reverted.\n";

        return false;
    }
}
