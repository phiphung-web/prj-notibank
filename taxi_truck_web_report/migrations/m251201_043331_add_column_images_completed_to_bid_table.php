<?php

use yii\db\Migration;

class m251201_043331_add_column_images_completed_to_bid_table extends Migration
{
    /**
     * {@inheritdoc}
     */
 public function safeUp()
    {
        $this->addColumn('{{%bid}}', 'pickup_images', $this->text()->null()->comment('Danh sách ảnh pickup dạng JSON'));
        $this->addColumn('{{%bid}}', 'dropoff_images', $this->text()->null()->comment('Danh sách ảnh dropoff dạng JSON'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bid}}', 'pickup_images');
        $this->dropColumn('{{%bid}}', 'dropoff_images');
    }

}
