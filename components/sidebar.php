<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Enhanced Sidebar</title>
    <style>
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #ffffff;
            text-decoration: none;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .logo-text {
            display: flex;
            flex-direction: column;
        }

        .logo-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.2;
        }

        .logo-subtitle {
            font-size: 0.75rem;
            color: #a0a0a0;
            font-weight: 400;
        }

        /* User Profile Card */
        .user-profile {
            padding: 1rem 1.5rem;
            margin: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            color: #808080;
            font-size: 0.75rem;
        }

        /* Menu Container */
        .sidebar-menu {
            list-style: none;
            padding: 1.5rem 0;
            margin: 0;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        .menu-item {
            margin: 0.3rem 0.75rem;
            position: relative;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 0.9rem 1rem;
            color: #b0b0b0;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }

        .menu-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .menu-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            transform: translateX(3px);
        }

        .menu-link:hover::before {
            transform: scaleY(1);
        }

        .menu-link.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
        }

        .menu-link.active::before {
            transform: scaleY(1);
        }

        .menu-icon {
            width: 24px;
            height: 24px;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .menu-link:hover .menu-icon {
            transform: scale(1.1);
        }

        /* Logout Section */
        .logout-section {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0.75rem;
            background: rgba(0, 0, 0, 0.2);
        }

        .logout-item {
            margin: 0;
        }

        .logout-item .menu-link {
            color: #ff6b6b;
        }

        .logout-item .menu-link:hover {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #ffffff;
            border: none;
            padding: 0.75rem 1rem;
            cursor: pointer;
            font-size: 1.3rem;
            transition: all 0.3s ease;
            display: none;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .mobile-toggle:hover {
            background: linear-gradient(135deg, #2d2d2d 0%, #3d3d3d 100%);
            transform: scale(1.05);
        }

        .mobile-toggle:active {
            transform: scale(0.95);
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(2px);
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .mobile-toggle {
                display: block;
            }

            .sidebar-header {
                padding: 1.5rem 1rem;
            }

            .logo-icon {
                width: 40px;
                height: 40px;
            }
        }

        /* Smooth Scrollbar */
        .sidebar-menu {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        /* Animation for menu items */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .menu-item {
            animation: slideIn 0.3s ease forwards;
            opacity: 0;
        }

        .menu-item:nth-child(1) {
            animation-delay: 0.05s;
        }

        .menu-item:nth-child(2) {
            animation-delay: 0.1s;
        }

        .menu-item:nth-child(3) {
            animation-delay: 0.15s;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Header with Logo -->
        <div class="sidebar-header">
            <a href="#" class="sidebar-logo">
                <div class="logo-text">
                    <div class="logo-title">
                        Anemia Diagnosis
                    </div>
                    <div class="logo-subtitle">Predicting System</div>
                </div>
            </a>
        </div>

        <!-- User Profile -->
        <div class="user-profile">
            <div class="user-avatar">
                <?php
                if (isset($_SESSION['user_email'])) {
                    echo strtoupper(substr($_SESSION['user_email'], 0, 1));
                } else {
                    echo 'U';
                }
                ?>
            </div>
            <div class="user-info">
                <div class="user-name">
                    <?php
                    if (isset($_SESSION['user_email'])) {
                        echo htmlspecialchars($_SESSION['user_email']);
                    } else {
                        echo 'Guest User';
                    }
                    ?>
                </div>
                <div class="user-role">Currnt User</div>
            </div>
        </div>

        <!-- Menu Items -->
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="dashboard.php" class="menu-link active">
                    <span class="menu-icon">
                        <i class="fa-solid fa-gauge"></i>
                    </span>
                    Dashboard
                </a>
            </li>

            <li class="menu-item">
                <a href="results.php" class="menu-link">
                    <span class="menu-icon">
                        <i class="fa-solid fa-floppy-disk"></i>
                    </span>
                    Saved Results
                </a>
            </li>

            <li class="menu-item">
                <a href="support.php" class="menu-link">
                    <span class="menu-icon icon-help"></span>
                    Help & Support
                </a>
            </li>
        </ul>

        <!-- Logout Section -->
        <div class="logout-section">
            <a href="#" class="menu-link">
                <span class="menu-icon icon-logout">
                </span>
                Logout
            </a>
        </div>
    </div>

    <!-- Mobile Toggle -->
    <button class="mobile-toggle" id="mobileToggle">☰</button>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <script>
        // Sidebar JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.getElementById('mobileToggle');
            const overlay = document.getElementById('overlay');

            // Toggle sidebar on mobile
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            });

            // Close sidebar when clicking overlay
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            });

            // Close sidebar when clicking a link on mobile
            if (window.innerWidth <= 768) {
                const menuLinks = document.querySelectorAll('.menu-link');
                menuLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar.classList.remove('open');
                        overlay.classList.remove('active');
                    });
                });
            }
        });
    </script>
</body>

</html>