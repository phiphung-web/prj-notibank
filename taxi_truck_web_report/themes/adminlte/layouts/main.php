<?php

use app\models\SystemConfiguration;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
$role = (isset(Yii::$app->controller->roleCurrentUser) ? Yii::$app->controller->roleCurrentUser : []);

if (Yii::$app->controller->action->id === 'login') {
    /**
     * Do not use this code in your template. Remove it.
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {
    if (class_exists('backend\assets\AppAsset')) {
        backend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);

    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    $this->registerJsFile('js/library.js', [
        'depends' => [\yii\web\YiiAsset::class],
        'version' => filemtime(Yii::getAlias('@webroot') . '/js/library.js')
    ]);

    $systemConfiguration = SystemConfiguration::getAllConfigurations(); ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">

    <head>
        <meta charset="<?= Yii::$app->charset ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <script>
            const TYPE_OF_CAR_LIST = JSON.parse('<?= json_encode(TYPE_OF_CAR_LIST) ?>')
            const VAT_VALUE = <?= (float)(isset($systemConfiguration['other_price_vat']) ? $systemConfiguration['other_price_vat'] : 0) ?>
        </script>
    </head>

    <body class="hold-transition skin-blue sidebar-mini">
        <?php $this->beginBody() ?>
        <div class="wrapper">

            <?= $this->render(
        'header.php',
        ['directoryAsset' => $directoryAsset]
    ) ?>

            <?= $this->render(
        'left.php',
        ['directoryAsset' => $directoryAsset]
    )
            ?>

            <?= $this->render(
                'content.php',
                ['content' => $content, 'directoryAsset' => $directoryAsset]
            ) ?>

        </div>
        <?php $this->endBody() ?>
        <?php
        if (isset($this->params['module']) && ! empty($this->params['module'])) {
            echo '<script src="/js/pages/' . $this->params['module'] . '.js"></script>';
        } ?>
    </body>

    </html>
    <?php $this->endPage() ?>
<?php
} ?>
