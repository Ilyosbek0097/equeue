<?php

namespace app\modules\equeue\assets;

use yii\web\AssetBundle;

class AdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/modules/equeue/web/assets';

    // All CSS files are loaded in the <head>
    public $css = [
        // Fonts
        'https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap',
        'vendor/fonts/boxicons.css',
        // Core CSS
        'vendor/css/core.css',
        'vendor/css/theme-default.css',
        'css/demo.css',
        // Vendor CSS
        'vendor/libs/perfect-scrollbar/perfect-scrollbar.css',
        'vendor/libs/apex-charts/apex-charts.css',
        'css/sweetalert.css',
        'css/select2.css',
    ];

    // All JS files are loaded at the end of the <body>
    public $js = [
        // Core
        'vendor/js/helpers.js',
        'js/config.js',
        // Popper is required by Bootstrap
        'vendor/libs/popper/popper.js',
        // Bootstrap JS is what makes dropdowns work
        'vendor/js/bootstrap.js',
        'vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
        // Theme specific
        'vendor/js/menu.js',
        'js/main.js',
        // Vendor JS
        'vendor/libs/apex-charts/apex-charts.js',
        'js/sweetalert.js',
        'js/select2.js',
    ];

    public $depends = [
        // This dependency ensures that jQuery is loaded BEFORE any of the files in the $js array above.
        // This is the most important part for fixing JS errors.
        'yii\web\YiiAsset',
        // If you were using Bootstrap 4/5 assets provided by Yii, you would use this instead of manual files.
        // 'yii\bootstrap5\BootstrapPluginAsset',
    ];
}
