<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <!-- Bootstrap Css -->
    <link href="templates/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="templates/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css -->
    <link href="templates/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- DataTables -->
    <link href="templates/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="templates/assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <!-- Sweet Alert -->
    <link href="templates/assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body data-sidebar="dark">
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-flex align-items-center justify-content-between">
                                <h4 class="mb-0"><?php echo $pageTitle; ?></h4>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="templates/assets/libs/jquery/jquery.min.js"></script>
    <script src="templates/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="templates/assets/libs/metismenu/metisMenu.min.js"></script>
    <script src="templates/assets/libs/simplebar/simplebar.min.js"></script>
    <script src="templates/assets/libs/node-waves/waves.min.js"></script>
    <!-- DataTables -->
    <script src="templates/assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="templates/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="templates/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="templates/assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <!-- Sweet Alert -->
    <script src="templates/assets/libs/sweetalert2/sweetalert2.min.js"></script>
    <!-- CRUD Manager -->
    <script src="js/crud.js"></script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
    <?php if (isset($initScript)) echo $initScript; ?>
</body>
</html>
