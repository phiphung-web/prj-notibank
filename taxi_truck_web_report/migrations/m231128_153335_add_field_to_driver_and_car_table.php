<?php

use yii\db\Migration;

/**
 * Class m231128_153335_add_field_to_driver_and_car_table
 */
class m231128_153335_add_field_to_driver_and_car_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('driver', 'email', $this->string(255)->after('username'));
        $this->renameColumn('driver', 'address', 'address_contact');
        $this->addColumn('driver', 'address_permanent', $this->text()->after('address_contact'));
        $this->addColumn('driver', 'certificate_type', $this->string(2)->after('address_permanent'));
        $this->addColumn('driver', 'activity_area', $this->string(255)->after('certificate_type'));
        $this->addColumn('driver', 'referral_code', $this->string(255)->after('activity_area'));
        $this->addColumn('driver', 'identity_card', $this->string(255)->after('referral_code'));
        $this->addColumn('driver', 'identity_card_created_on', $this->date()->after('identity_card'));
        $this->addColumn('driver', 'identity_card_created_at', $this->text()->after('identity_card_created_on'));
        $this->addColumn('driver', 'point', $this->float()->after('referral_code')->defaultValue(10));
        $this->renameColumn('driver', 'album', 'avatar');
        $this->addColumn('driver', 'album_id_card', $this->text()->after('avatar'));
        $this->addColumn('driver', 'album_driving_license', $this->text()->after('album_id_card'));
        $this->addColumn('car', 'car_in', $this->text());
        $this->addColumn('car', 'car_out', $this->text());
        $this->addColumn('car', 'car_front', $this->text());
        $this->addColumn('car', 'car_behind', $this->text());
        $this->addColumn('car', 'album_vehicle_certificate', $this->text()->after('car_behind'));
        $this->addColumn('car', 'album_registration_certificate', $this->text()->after('album_vehicle_certificate'));
        $this->addColumn('car', 'album_insurance', $this->text()->after('album_registration_certificate'));
    }

    public function safeDown()
    {
        $this->dropColumn('car', 'album_insurance');
        $this->dropColumn('car', 'album_registration_certificate');
        $this->dropColumn('car', 'album_vehicle_certificate');
        $this->dropColumn('car', 'car_in');
        $this->dropColumn('car', 'car_out');
        $this->dropColumn('car', 'car_front');
        $this->dropColumn('car', 'car_behind');
        $this->renameColumn('driver', 'album_driving_license', 'album');
        $this->renameColumn('driver', 'album_id_card', 'album');
        $this->renameColumn('driver', 'avatar', 'album');
        $this->dropColumn('driver', 'point');
        $this->dropColumn('driver', 'identity_card');
        $this->dropColumn('driver', 'identity_card_created_on');
        $this->dropColumn('driver', 'identity_card_created_at');
        $this->dropColumn('driver', 'referral_code');
        $this->dropColumn('driver', 'activity_area');
        $this->dropColumn('driver', 'certificate_type');
        $this->dropColumn('driver', 'address_permanent');
        $this->renameColumn('driver', 'address_contact', 'address');
        $this->dropColumn('driver', 'email');
    }
}
