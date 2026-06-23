<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SearchPayTransaction represents the model behind the search form of `app\models\PayTransaction`.
 */
class SearchSmsPayTransaction extends PayTransaction
{
    public $createTimeRange;
    public $isAll;
    public $createTimeStart;
    public $createTimeEnd;
    public $keyword;
    public $user_id;

    public function behaviors()
    {
        return [
            [
                'class' => DateRangeBehavior::className(),
                'attribute' => 'createTimeRange',
                'dateStartAttribute' => 'createTimeStart',
                'dateEndAttribute' => 'createTimeEnd',
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'money', 'driver_id', 'status', 'user_id'], 'integer'],
            [['isAll'], 'boolean'],
            [['keyword'], 'string'],
            [['created_on', 'modified_on', 'description'], 'safe'],
            [['createTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'status' => 'Trạng thái giao dịch',
            'createTimeRange' => 'Khoảng thời gian',
            'keyword' => 'Từ khóa',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $type)
    {
        $query = $this->find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        $this->load($params);
        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }
        if ($this->status != '') {
            $query->andFilterWhere([
                'pay_transaction.status' => $this->status,
            ]);
        }
        if ($type == 1) {
            $query->innerJoin('bank_transaction', 'admin_id = user_id and check_driver = ' . $type . ' and bank_transaction.type_bank = pay_transaction.type_bank');
        } else {
            $query->leftJoin('bank_transaction', 'admin_id = user_id');
            $query->andFilterWhere(['or', ['bank_transaction.check_driver' => $type], ['user_id' => 0]]);
        }
        if (! empty($this->keyword)) {
            $query->andFilterWhere(['like', 'pay_transaction.description', $this->keyword])
                ->orFilterWhere(['like', 'pay_transaction.phone', $this->keyword])
                ->orFilterWhere(['like', 'driver.display_name', $this->keyword]);
        }
        $query->andFilterWhere(['pay_transaction.type' => PAY_SMS]);
        $query->andFilterWhere(['pay_transaction.is_disabled' => 0]);
        if (isset($_GET) && is_array($_GET) && count($_GET) && ! isset($_GET['isAll']) && isset($_GET['SearchSmsPayTransaction']['createTimeRange'])) {
            $query->andFilterWhere(['>=', 'pay_transaction.created_on', date('Y-m-d 00:00:00', $this->createTimeStart)])
                ->andFilterWhere(['<=', 'pay_transaction.created_on', date('Y-m-d 23:59:59', $this->createTimeEnd)]);
        }

        if ($this->user_id) {
            $query->andFilterWhere([
                'pay_transaction.user_id' => $this->user_id,
            ]);
        }

        $query->joinWith(['driver', 'admin']);
        $query->orderBy(['pay_transaction.created_on' => SORT_DESC]);

        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(Driver::className(), ['id' => 'driver_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdmin()
    {
        return $this->hasOne(Admin::className(), ['id' => 'admin_id_accepted']);
    }
}
