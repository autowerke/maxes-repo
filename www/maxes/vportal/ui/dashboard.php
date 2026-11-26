<?php require_once 'includes/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/dashboard.css<?= getCacheBustingVersion('/maxtest/vportal/ui/css/dashboard.css') ?>">
    <link rel="stylesheet" href="css/style.css<?= getCacheBustingVersion('/maxtest/vportal/ui/css/style.css') ?>">

</head>
<body>
    <div id="header-panel">
        <div class="header-container">
            <div class="header-left">
                <div class="logo">VENDOR PORTAL</div>
                <div class="header-title">RFQ Management</div>
            </div>
            <div class="header-right">
                <div id="user-info"></div>
                <button id="logout-button">Logout</button>
            </div>
        </div>
    </div>

    <div id="main-content">
        <div id="rfq-list-panel">
            <div class="panel-header">
                <h2>RFQ Invitations</h2>
                <div class="panel-subtext">Respond to active quotation requests</div>
            </div>
            <div class="panel-content">
                <table id="rfq-table" class="display">
                </table>
            </div>
        </div>
        <div id="rfq-detail-panel">
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <p>Select an RFQ to view details and submit your quotation</p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    
    <script src="js/modules/auth.js<?= getCacheBustingVersion('/maxtest/vportal/ui/js/modules/auth.js') ?>"></script>
    <script src="js/modules/app.js<?= getCacheBustingVersion('/maxtest/vportal/ui/js/modules/app.js') ?>"></script>

</body>
</html>