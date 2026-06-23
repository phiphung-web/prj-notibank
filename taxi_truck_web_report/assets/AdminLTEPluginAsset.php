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
class AdminLTEPluginAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/plugins';

    public $css = [
         'bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
    ];

    public $js = [
    'bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
    ];

    public $depends = [
    'yii\jui\JuiAsset',
    ];
}
