<?php

namespace app\models;

/**
 * This is the model class for table "system_configuration".
 *
 * @property int $id
 * @property string $keyword
 * @property text $content
 */
class SystemConfiguration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'system_configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on'], 'safe'],
            [['keyword'], 'string', 'max' => 255],
            [['content'], 'string'],
        ];
    }

    /**
     * Get all configurations as an array
     *
     * @return array
     */
    public static function getAllConfigurations()
    {
        $configurations = self::find()->select(['keyword', 'content'])->asArray()->all();
        $result = [];
        if (isset($configurations) && is_array($configurations) && count($configurations)) {
            foreach ($configurations as $config) {
                $result[$config['keyword']] = $config['content'];
            }
        }

        return $result;
    }

    /**
     * Search system configuration by keyword.
     *
     * @param string
     * @return string|null
     */
    public static function findByKeyword($keyword = '')
    {
        return SystemConfiguration::find()
            ->select('content')
            ->where(['keyword' => $keyword])
            ->scalar();
    }
}
