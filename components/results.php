<?php
session_start();
require_once '../backend/config.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$classification_filter = isset($_GET['classification']) ? trim($_GET['classification']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(patient_name LIKE ? OR notes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= 'ss';
}

if (!empty($classification_filter)) {
    $where_conditions[] = "classification = ?";
    $params[] = $classification_filter;
    $param_types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(analysis_date) >= ?";
    $params[] = $date_from;
    $param_types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(analysis_date) <= ?";
    $params[] = $date_to;
    $param_types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM diagnosis_results $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get results with pagination
$sql = "SELECT * FROM diagnosis_results $where_clause ORDER BY analysis_date DESC LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();

// Get unique classifications for filter dropdown
$class_sql = "SELECT DISTINCT classification FROM diagnosis_results ORDER BY classification";
$class_result = $conn->query($class_sql);
$classifications = [];
while ($row = $class_result->fetch_assoc()) {
    $classifications[] = $row['classification'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Results - Hematology Diagnosis System</title>
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/results.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>Saved Results</h1>
            <p>View and manage all saved diagnosis results</p>
        </div>

        <!-- Filters and Search -->
        <div class="filters-container">
            <form method="GET" class="filters-form" id="filtersForm">
                <div class="search-group">
                    <input type="text" 
                           name="search" 
                           placeholder="Search by patient name or notes..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="search-input">
                    <button type="submit" class="search-btn">Search</button>
                </div>
                
                <div class="filter-group">
                    <select name="classification" class="filter-select">
                        <option value="">All Classifications</option>
                        <?php foreach ($classifications as $class): ?>
                            <option value="<?php echo htmlspecialchars($class); ?>" 
                                    <?php echo $classification_filter === $class ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="date" 
                           name="date_from" 
                           value="<?php echo htmlspecialchars($date_from); ?>"
                           class="date-input"
                           placeholder="From Date">
                    
                    <input type="date" 
                           name="date_to" 
                           value="<?php echo htmlspecialchars($date_to); ?>"
                           class="date-input"
                           placeholder="To Date">
                    
                    <button type="submit" class="filter-btn">Filter</button>
                    <a href="results.php" class="clear-btn">Clear</a>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div class="summary-container">
            <div class="summary-card">
                <div class="summary-number"><?php echo $total_records; ?></div>
                <div class="summary-label">Total Results</div>
            </div>
            <div class="summary-card">
                <div class="summary-number"><?php echo $total_pages; ?></div>
                <div class="summary-label">Pages</div>
            </div>
            <div class="summary-card">
                <div class="summary-number"><?php echo count($classifications); ?></div>
                <div class="summary-label">Classifications</div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="results-table-container">
            <?php if ($results->num_rows > 0): ?>
                <div class="table-wrapper">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Classification</th>
                                <th>Confidence</th>
                                <th>Analysis Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $results->fetch_assoc()): ?>
                                <tr>
                                    <td class="id-cell">#<?php echo $row['id']; ?></td>
                                    <td class="patient-cell">
                                        <div class="patient-name">
                                            <?php echo htmlspecialchars($row['patient_name'] ?: 'Anonymous'); ?>
                                        </div>
                                    </td>
                                    <td class="classification-cell">
                                        <span class="classification-badge">
                                            <?php echo htmlspecialchars($row['classification']); ?>
                                        </span>
                                    </td>
                                    <td class="confidence-cell">
                                        <div class="confidence-display">
                                            <span class="confidence-text"><?php echo htmlspecialchars($row['confidence_score']); ?></span>
                                            <div class="mini-confidence-bar">
                                                <div class="mini-confidence-fill" 
                                                     style="width: <?php echo str_replace('%', '', $row['confidence_score']); ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="date-cell">
                                        <div class="date-display">
                                            <div class="date-main">
                                                <?php echo date('M d, Y', strtotime($row['analysis_date'])); ?>
                                            </div>
                                            <div class="date-time">
                                                <?php echo date('H:i', strtotime($row['analysis_date'])); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <button class="action-btn view-btn" 
                                                onclick="viewResult(<?php echo $row['id']; ?>)">
                                            View
                                        </button>
                                        <button class="action-btn delete-btn" 
                                                onclick="deleteResult(<?php echo $row['id']; ?>)">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                                   class="page-btn prev-btn">Previous</a>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            if ($start > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" 
                                   class="page-btn">1</a>
                                <?php if ($start > 2): ?>
                                    <span class="page-dots">...</span>
                                <?php endif; ?>
                            <?php endif;

                            for ($i = $start; $i <= $end; $i++): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="page-btn <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor;

                            if ($end < $total_pages): ?>
                                <?php if ($end < $total_pages - 1): ?>
                                    <span class="page-dots">...</span>
                                <?php endif; ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" 
                                   class="page-btn"><?php echo $total_pages; ?></a>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                                   class="page-btn next-btn">Next</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pagination-info">
                            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $records_per_page, $total_records); ?> 
                            of <?php echo $total_records; ?> results
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">📄</div>
                    <div class="no-results-title">No Results Found</div>
                    <div class="no-results-text">
                        <?php if (!empty($search) || !empty($classification_filter) || !empty($date_from) || !empty($date_to)): ?>
                            No results match your current filters. Try adjusting your search criteria.
                        <?php else: ?>
                            You haven't saved any diagnosis results yet. Start by analyzing some CBC images.
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($search) || !empty($classification_filter) || !empty($date_from) || !empty($date_to)): ?>
                        <a href="saved-results.php" class="clear-filters-btn">Clear All Filters</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Result Modal -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Diagnosis Result Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content delete-modal">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this diagnosis result? This action cannot be undone.</p>
                <div class="modal-actions">
                    <button class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
                    <button class="confirm-delete-btn" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div class="message-container" id="messageContainer"></div>

    <script src="../js/results.js"></script>
    <script src="../js/sidebar.js"></script>
</body>
</html>