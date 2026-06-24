<?php

use yii\db\Migration;

/**
 * Class m260116_042130_update_trip_group_table_remove_point_and_vehicle_type_add_type_of_car
 */
class m260116_042130_update_trip_group_table_remove_point_and_vehicle_type_add_type_of_car extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Xóa cột point
        $this->dropColumn('trip_group', 'point');

        // Xóa cột vehicle_type
        $this->dropColumn('trip_group', 'vehicle_type');

        // Thêm cột type_of_car (integer)
        $this->addColumn('trip_group', 'type_of_car', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Xóa cột type_of_car
        $this->dropColumn('trip_group', 'type_of_car');

        // Thêm lại cột vehicle_type
        $this->addColumn('trip_group', 'vehicle_type', $this->string(255)->null());

        // Thêm lại cột point
        $this->addColumn('trip_group', 'point', $this->decimal(10, 2)->null());

        return false;
    }
}
