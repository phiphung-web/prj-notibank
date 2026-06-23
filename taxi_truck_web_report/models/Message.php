<?php

namespace app\models;

/**
 * This is the model class for table "message".
 *
 * @property int $id
 * @property string $phone
 * @property string $title
 * @property string $content
 * @property string $time
 */
class Message extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['title', 'content'], 'match', 'pattern' => '/^[\p{L}\p{N}\s.,!?;:()\'"\/\\-@#%&*+=_<>\[\]{}|~•…–—]+$/u', 'message' => '{attribute} không được chứa biểu tượng (emoji) hoặc ký tự lạ.'],
            [['content'], 'string'],
            [['time'], 'safe'],
            [['phone'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Tài xế',
            'title' => 'Tiêu đề',
            'content' => 'Nội dung',
            'time' => 'Thời gian',
        ];
    }
}
