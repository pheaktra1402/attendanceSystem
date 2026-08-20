<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Attendance System' : 'Attendance System'; ?></title>
    
    <!-- Google Fonts (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-wrapper {
            flex: 1;
            padding-bottom: 40px;
        }

        /* Navbar Customization */
        .navbar-custom {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .navbar-custom .navbar-brand, 
        .navbar-custom .nav-link {
            color: #f8fafc;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            color: #38bdf8 !important;
        }

        /* Card Customization */
        .card-stat {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }

        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Status Badges */
        .badge-present {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 600;
        }

        .badge-absent {
            background-color: #fee2e2;
            color: #b91c1c;
            font-weight: 600;
        }

        .badge-late {
            background-color: #fef3c7;
            color: #b45309;
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/navbar.php'; ?>

<div class="main-wrapper container py-4">
    <?php 
    // Automatically render any flash messages if set
    display_flash_message(); 
    ?>