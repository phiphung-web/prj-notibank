<?php

namespace app\services;

use app\helpers\MyStringHelper;
use app\models\BankTransaction;
use yii\db\Query;
use Yii;

class BankTransactionService
{
    public function __construct()
    {
    }

    public function getBankTransactionList($admin)
    {
        return BankTransaction::find()->where(['admin_id' => $admin->id])->asArray()->all();
    }

    public function getBankTransaction($admin, $type_bank)
    {
        return BankTransaction::find()->where(['admin_id' => $admin->id, 'type_bank' => $type_bank])->asArray()->one();
    }

    public function getAdminList($check_driver = true)
    {
        $admins = (new Query())
            ->select(['admin.*', 'bank_transaction.type_bank', 'bank_transaction.account_balance'])
            ->from('admin')
            ->innerJoin('bank_transaction', 'bank_transaction.admin_id = admin.id')
            ->where(['bank_transaction.check_driver' => $check_driver])
            ->orderBy('bank_transaction.type_bank asc, admin.id asc')
            ->all();

        return $admins;
    }

    public function updateBankTransaction($admin, $request): bool
    {

        BankTransaction::deleteAll(['admin_id' => $admin->id]);

        $used = [];
        foreach ($request['bank_transaction'] ?? [] as $typeBank => $data) {
            if (empty($data['token_tele']) && empty($data['chat_tele']) && empty($data['webhook_discord_url'])) {
                continue;
            }

            $t = new BankTransaction();
            $t->admin_id        = $admin->id;
            $t->type_bank       = $typeBank ?? 0;
            $t->token_tele      = $data['token_tele'] ?? '';
            $t->chat_tele       = $data['chat_tele'] ?? '';
            $t->account_holder  = $data['account_holder'] ?? '';
            $t->account_number  = $data['account_number'] ?? '';
            $t->check_driver    = $data['check_driver'] ?? 0;
            $t->is_display      = $data['is_display'] ?? 0;
            $t->account_balance = MyStringHelper::convertStringToInteger($data['account_balance']);
            $t->created_on      = date('Y-m-d H:i:s');
            $t->updated_on      = date('Y-m-d H:i:s');
            if (!empty($data['qrcode_path'])) {
                $t->qrcode_path = $data['qrcode_path'];
            } else {
                $t->qrcode_path = null;
            }
            $t->save(false);
        }

        return true;
    }
}