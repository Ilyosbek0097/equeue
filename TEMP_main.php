<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\equeue\assets\AdminAsset;

// The asset bundle is registered here. Yii will now automatically
// add all the CSS to the <head> and all the JS to the end of the <body>
// in the correct order.
AdminAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="/modules/equeue/web/assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="<?= Yii::$app->charset ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title><?= Html::encode($this->title) ?> - Hamkor Bank Navbat</title>
  <?= Html::csrfMetaTags() ?>
  <link rel="icon" type="image/x-icon" href="<?= Url::to('@web/modules/equeue/web/assets/img/favicon/favicon.ico') ?>" />

  <!-- All CSS files are now loaded automatically by the line below -->
  <?php $this->head() ?>
</head>
<body>
  <?php $this->beginBody() ?>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

      <!-- Menu -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="<?= Url::to(['site/index']) ?>" class="app-brand-link">
            <span class="app-brand-logo demo">
              <img src="<?= Url::to('@web/modules/equeue/web/assets/img/logo/logo.png') ?>" alt="Logo" class="w-px-180 h-auto" />
            </span>
          </a>
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>

        <!-- Your menu items remain the same -->
        <ul class="menu-inner py-1">
          <li class="menu-item <?= Yii::$app->controller->route == 'equeue/site/index' ? 'active' : '' ?>">
            <a href="<?= Url::to(['site/index']) ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-home-circle"></i><div>Dashboard</div></a>
          </li>
          <li class="menu-item <?= Yii::$app->controller->route == 'equeue/site/services' ? 'active' : '' ?>">
            <a href="<?= Url::to(['site/services']) ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-window"></i><div>Xizmatlar</div></a>
          </li>
          <li class="menu-item <?= Yii::$app->controller->route == 'equeue/site/employes' ? 'active' : '' ?>">
            <a href="<?= Url::to(['site/employes']) ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-user"></i><div>Xodimlar</div></a>
          </li>
          <li class="menu-item <?= Yii::$app->controller->route == 'equeue/site/queues' ? 'active' : '' ?>">
            <a href="<?= Url::to(['site/queues']) ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-hourglass"></i><div>Navbatlar</div></a>
          </li>
          <li class="menu-item <?= Yii::$app->controller->route == 'equeue/site/branches' ? 'active' : '' ?>">
            <a href="<?= Url::to(['site/branches']) ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-cube"></i><div>Filiallar</div></a>
          </li>
          <li class="menu-item <?= Yii::$app->controller->route == 'equeue/site/bxoes' ? 'active' : '' ?>">
            <a href="<?= Url::to(['site/bxoes']) ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-file"></i><div>Bxolar</div></a>
          </li>
          <li class="menu-item <?= Yii::$app->controller->route == 'equeue/site/counters' ? 'active' : '' ?>">
            <a href="<?= Url::to(['site/counters']) ?>" class="menu-link"><i class="menu-icon tf-icons bx bx-window"></i><div>Oynalar</div></a>
          </li>
        </ul>
      </aside>
      <!-- / Menu -->

      <div class="layout-page">
        <!-- Navbar -->
        <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)"><i class="bx bx-menu bx-sm"></i></a>
          </div>
          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <!-- User Dropdown -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online"><img src="<?= Url::to('@web/modules/equeue/web/assets/img/avatars/1.png') ?>" alt class="w-px-40 h-auto rounded-circle" /></div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online"><img src="<?= Url::to('@web/modules/equeue/web/assets/img/avatars/1.png') ?>" alt class="w-px-40 h-auto rounded-circle" /></div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-semibold d-block">Foydalanuvchi</span>
                          <small class="text-muted">Admin</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li><div class="dropdown-divider"></div></li>
                  <li><a class="dropdown-item" href="#"><i class="bx bx-user me-2"></i><span class="align-middle">Mening Profilim</span></a></li>
                  <li><a class="dropdown-item" href="#"><i class="bx bx-cog me-2"></i><span class="align-middle">Sozlamalar</span></a></li>
                  <li><div class="dropdown-divider"></div></li>
                  <li><a class="dropdown-item" href="<?= Url::to(['site/logout']) ?>"><i class="bx bx-power-off me-2"></i><span class="align-middle">Chiqish</span></a></li>
                </ul>
              </li>
              <!--/ User Dropdown -->
            </ul>
          </div>
        </nav>
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <?= $content ?>
          </div>
          <!-- / Content -->

          <!-- Footer -->
          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">
                © <script>document.write(new Date().getFullYear());</script>, Hamkor Bank
              </div>
            </div>
          </footer>
          <!-- / Footer -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- / Content wrapper -->
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>

  <!-- All JS files, including jQuery, are now loaded automatically by the line below -->
  <?php $this->endBody() ?>

  <!-- Any page-specific inline scripts can go here -->
  <script>
    $(document).ready(function() {
        // This will work now because jQuery is guaranteed to be loaded before it runs.
        if ($.fn.select2) {
          $('.select2').select2();
        }
    });
  </script>
</body>
</html>
<?php $this->endPage() ?>
