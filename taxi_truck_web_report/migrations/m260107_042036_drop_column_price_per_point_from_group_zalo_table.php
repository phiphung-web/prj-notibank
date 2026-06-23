<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%column_price_per_point_from_group_zalo}}`.
 */
class m260107_042036_drop_column_price_per_point_from_group_zalo_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%group_zalo}}', 'price_per_point');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(
            '{{%group_zalo}}',
            'price_per_point',
            $this->float()->notNull()->defaultValue(0)
        );
    }
}
