<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <title>Fresh Sidebar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #f8f9fa;
        }

        /* Sidebar Container */
        .sidebar {
            width: 290px;
            background: #ffffff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid #e8ebed;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        /* Logo Area */
        .sidebar-header {
            padding: 2.5rem 1.8rem 1.5rem;
            border-bottom: 1px solid #f0f2f4;
        }

        .brand-container {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1a1f36;
            letter-spacing: -0.02em;
            line-height: 1.3;
        }

        .brand-subtitle {
            font-size: 0.8rem;
            color: #7c8b9a;
            font-weight: 500;
        }

        /* User Card */
        .user-card {
            margin: 1.5rem 1.2rem;
            padding: 1rem;
            background: #f7f9fb;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            border: 1px solid #e8ebed;
            transition: all 0.25s ease;
        }

        .user-card:hover {
            background: #eef2f6;
            border-color: #d8dfe6;
            cursor: pointer;
        }

        .avatar-circle {
            width: 44px;
            height: 44px;
            background: linear-gradient(145deg, #467be5ff, #6399f1ff);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.15);
        }

        .user-details {
            flex: 1;
            min-width: 0;
        }

        .user-name-text {
            color: #1a1f36;
            font-weight: 600;
            font-size: 0.92rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-label {
            color: #7c8b9a;
            font-size: 0.75rem;
            margin-top: 0.15rem;
        }

        /* Navigation Menu */
        .nav-menu {
            list-style: none;
            padding: 0.8rem 1.2rem;
            margin: 0;
            flex: 1;
            overflow-y: auto;
        }

        .nav-menu::-webkit-scrollbar {
            width: 5px;
        }

        .nav-menu::-webkit-scrollbar-thumb {
            background: #d8dfe6;
            border-radius: 3px;
        }

        .nav-item {
            margin-bottom: 0.4rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.1rem;
            color: #5a6c7d;
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.94rem;
            border-radius: 11px;
            position: relative;
        }

        .nav-link:hover {
            background: #f0f3f7;
            color: #1a1f36;
        }

        .nav-link.active-page {
            background: #eef2ff;
            color: #467be5ff;
            font-weight: 600;
        }

        .nav-link.active-page::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 60%;
            width: 3px;
            background: #467be5ff;
            border-radius: 0 2px 2px 0;
        }

        .link-icon {
            width: 20px;
            height: 20px;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        /* Bottom Section */
        .sidebar-footer {
            border-top: 1px solid #f0f2f4;
            padding: 1.2rem;
        }

        .logout-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.1rem;
            color: #dc2626;
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.94rem;
            border-radius: 11px;
        }

        .logout-link:hover {
            background: #fef2f2;
        }

        /* Mobile Elements */
        .menu-btn {
            position: fixed;
            top: 1.2rem;
            left: 1.2rem;
            z-index: 1001;
            background: #ffffff;
            color: #1a1f36;
            border: 1px solid #e8ebed;
            padding: 0.7rem 1rem;
            cursor: pointer;
            font-size: 1.4rem;
            transition: all 0.2s ease;
            display: none;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .menu-btn:hover {
            background: #f7f9fb;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .menu-btn:active {
            transform: scale(0.96);
        }

        .backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .menu-btn {
                display: block;
            }
        }

        /* Animations */
        @keyframes fadeSlideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-item {
            animation: fadeSlideIn 0.4s ease forwards;
            opacity: 0;
        }

        .nav-item:nth-child(1) { animation-delay: 0.05s; }
        .nav-item:nth-child(2) { animation-delay: 0.1s; }
        .nav-item:nth-child(3) { animation-delay: 0.15s; }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="mainSidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <div class="brand-container">
                <div class="brand-title">Anemia Diagnosis</div>
                <div class="brand-subtitle">Predicting System</div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="user-card">
            <div class="avatar-circle">
                <?php
                if (isset($_SESSION['user_email'])) {
                    echo strtoupper(substr($_SESSION['user_email'], 0, 1));
                } else {
                    echo 'U';
                }
                ?>
            </div>
            <div class="user-details">
                <div class="user-name-text">
                    <?php
                    if (isset($_SESSION['user_email'])) {
                        echo htmlspecialchars($_SESSION['user_email']);
                    } else {
                        echo 'Guest User';
                    }
                    ?>
                </div>
                <div class="user-label">Current User</div>
            </div>
        </div>

        <!-- Navigation -->
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active-page' : ''; ?>">
                    <span class="link-icon">
                        <i class="fa-solid fa-gauge"></i>
                    </span>
                    Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="results.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'results.php') ? 'active-page' : ''; ?>">
                    <span class="link-icon">
                        <i class="fa-solid fa-floppy-disk"></i>
                    </span>
                    Saved Results
                </a>
            </li>

            <li class="nav-item">
                <a href="support.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'support.php') ? 'active-page' : ''; ?>">
                    <span class="link-icon">
                        <i class="fa-solid fa-circle-question"></i>
                    </span>
                    Help & Support
                </a>
            </li>
        </ul>

        <!-- Footer -->
        <div class="sidebar-footer">
            <a href="#" class="logout-link">
                <span class="link-icon">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </span>
                Logout
            </a>
        </div>
    </div>

    <!-- Mobile Button -->
    <button class="menu-btn" id="toggleBtn">☰</button>

    <!-- Backdrop -->
    <div class="backdrop" id="backdrop"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('mainSidebar');
            const toggleBtn = document.getElementById('toggleBtn');
            const backdrop = document.getElementById('backdrop');

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                backdrop.classList.toggle('show');
            });

            document.querySelector('.logout-link').addEventListener('click', function(e) {
                e.preventDefault();
                // Add your logout logic here
                let alert = confirm("Are you sure you want to logout?");
                
                setTimeout(()=>{
                    if (alert) {
                        window.location.href = 'logout.php';
                    }
                }, 300);
            });

            backdrop.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                backdrop.classList.remove('show');
            });

            if (window.innerWidth <= 768) {
                const navLinks = document.querySelectorAll('.nav-link, .logout-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar.classList.remove('mobile-open');
                        backdrop.classList.remove('show');
                    });
                });
            }
        });
    </script>
</body>

</html>