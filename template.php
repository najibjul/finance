<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords" content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="./public/img/icons/icon-48x48.png" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <link rel="canonical" href="https://demo-basic.adminkit.io/" />

    <title>Finance | <?= $title ?? '' ?></title>

    <?= $css ?? '' ?>
    <link href="./public/css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">



</head>

<body>
    <div class="wrapper">
        <nav id="sidebar" class="sidebar js-sidebar">
            <div class="sidebar-content js-simplebar">
                <a class="sidebar-brand" href="dashboard.php">
                    <div class="p-2 bg-white rounded">
                        <img src="./public/img/icons/express.jpg" class="img-fluid" alt="Express">
                    </div>
                </a>

                <ul class="sidebar-nav">
                    <li class="sidebar-header">
                        Dashboard
                    </li>

                    <li class="sidebar-item <?= $title == 'Dashboard' ? 'active' : '' ?>">
                        <a class="sidebar-link" href="dashboard.php">
                            <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Dashboard</span>
                        </a>
                    </li>

                    <li class="sidebar-header">
                        Report
                    </li>
                    <li class="sidebar-item <?= $title == 'Monitoring Report' ? 'active' : '' ?>">
                        <a class="sidebar-link" href="monitoring-report.php">
                            <i class="align-middle" data-feather="monitor"></i> <span class="align-middle">Monitoring Report</span>
                        </a>
                    </li>
                    <li class="sidebar-item <?= $title == 'Search Report' ? 'active' : '' ?>">
                        <a class="sidebar-link" href="search-report.php">
                            <i class="align-middle" data-feather="search"></i> <span class="align-middle">Search Report</span>
                        </a>
                    </li>
                    <?php if ($auth->role_id == 1) : ?>
                        <li class="sidebar-item <?= $title == 'New Report' ? 'active' : '' ?>">
                            <a class="sidebar-link" href="new-report.php">
                                <i class="align-middle" data-feather="file-plus"></i> <span class="align-middle">New Report</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($auth->role_id == 1) : ?>
                        <li class="sidebar-header">
                            Admin Menu
                        </li>
                        <li class="sidebar-item <?= $title == 'User Menu' ? 'active' : '' ?>">
                            <a class="sidebar-link" href="user-menu.php">
                                <i class="align-middle" data-feather="users"></i> <span class="align-middle">User Menu</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>

                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav navbar-align">
                        
                        <li class="nav-item dropdown">
                            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                                <i class="align-middle" data-feather="settings"></i>
                            </a>

                            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                                <span class="text-dark"><?= $auth->name ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="profile.php"><i class="align-middle me-1" data-feather="user"></i> Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Log out</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content">
                <div class="container-fluid p-0">
                    <?= $content; ?>
                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-start">
                            <p class="mb-0 text-muted">
                                <b>Express</b>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <?= $js ?? '' ?>

    <script src="./public/js/app.js"></script>
   
</body>

</html>