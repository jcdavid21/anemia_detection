<div class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="dashboard.php" class="menu-link active <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" id="dashboardLink">
                    <span class="menu-icon icon-dashboard"></span>
                    Dashboard
                </a>
            </li>
            <li class="menu-item">
                <a href="results.php" class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'results.php') ? 'active' : ''; ?>" id="resultsLink">
                    <span class="menu-icon icon-save"></span>
                    Saved Results
                </a>
            </li>
            <li class="menu-item">
                <a href="support.php" class="menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'support.php') ? 'active' : ''; ?>" id="supportLink">
                    <span class="menu-icon icon-help"></span>
                    Help & Support
                </a>
            </li>
            <li class="menu-item logout-item">
                <a href="#" class="menu-link">
                    <span class="menu-icon icon-logout"></span>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Mobile Toggle -->
    <button class="mobile-toggle" id="mobileToggle" style="display: none;">☰</button>
    
    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>