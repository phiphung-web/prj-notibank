<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DashboardAsset extends AssetBundle
{
    //public $sourcePath = 'web/js';
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
    'js/dashboard.js',
    ];

    public $depends = [
    'yii\jui\JuiAsset',
    'app\assets\AdminLTEPluginAsset',
    ];
}
