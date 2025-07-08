<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: logout.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hematology Diagnosis System</title>
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/diagnosis-system.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>Hematology Diagnosis System</h1>
            <p>Upload a CBC result image to get an AI-powered anemia classification</p>
        </div>

        <div class="upload-container">
            <div class="upload-zone" id="uploadZone">
                <div class="upload-icon">📋</div>
                <div class="upload-text">Drop your CBC result image here</div>
                <div class="upload-subtext">or click to browse files</div>
                <input type="file" id="fileInput" class="file-input" accept="image/*">
            </div>
            
            <div class="selected-file" id="selectedFile">
                <strong>Selected:</strong> <span id="fileName"></span>
            </div>

            <!-- Image Preview Section -->
            <div class="image-preview" id="imagePreview">
                <div class="preview-header">
                    <h3>Uploaded Image Preview</h3>
                </div>
                <div class="preview-image-container" id="previewContainer">
                    <img id="previewImage" src="" alt="Preview" class="preview-image">
                </div>
            </div>
            
            <div style="text-align: center;">
                <button class="analyze-btn" id="analyzeBtn" disabled>
                    Analyze Image
                </button>
            </div>

            <div class="error-message" id="errorMessage"></div>
        </div>

        <div class="loading-container" id="loadingContainer">
            <div class="spinner"></div>
            <div class="loading-text">Analyzing your CBC result...</div>
            <div class="loading-subtext">This may take a few moments while our AI processes the image</div>
        </div>

        <div class="results-container" id="resultsContainer">
            <div class="results-header">
                <h2>Analysis Results</h2>
            </div>
            <div class="results-content">
                <div class="results-grid">
                    <div class="classification-card">
                        <div class="classification-title">Classification</div>
                        <div class="classification-value" id="classificationValue">-</div>
                        <div class="confidence-container">
                            <div class="confidence-label">Confidence Level</div>
                            <div class="confidence-bar">
                                <div class="confidence-fill" id="confidenceFill"></div>
                            </div>
                            <div style="text-align: center; margin-top: 0.5rem;">
                                <strong id="confidenceText">0%</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-title">Confidence Visualization</div>
                        <div class="chart-wrapper">
                            <canvas id="confidenceChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                </div>

                <div class="explanation-section">
                    <div class="explanation-title">Analysis Explanation</div>
                    <div class="explanation-text" id="explanationText">-</div>
                </div>

                <div class="recommendations-section">
                    <div class="recommendations-title">Health Risk</div>
                    <ul class="recommendations-list" id="recommendationsList">
                        <li>No health risk available</li>
                    </ul>
                </div>

                <!-- Save Results Section -->
                <div class="save-section">
                    <div class="save-header">
                        <h3>Save Results</h3>
                        <p>Save this analysis to the database for future reference</p>
                    </div>
                    <div class="save-form">
                        <div class="form-group">
                            <label for="patientName">Patient Name (Optional)</label>
                            <input type="text" id="patientName" placeholder="Enter patient name">
                        </div>
                        <div class="form-group">
                            <label for="notes">Additional Notes (Optional)</label>
                            <textarea id="notes" placeholder="Enter any additional notes or observations"></textarea>
                        </div>
                        <button class="save-btn" id="saveBtn">
                            Save to Database
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Success Message -->
        <div class="save-message" id="saveMessage">
            <div class="message-content">
                <span class="message-icon">✅</span>
                <span class="message-text">Results saved successfully!</span>
            </div>
        </div>
    </div>

    <!-- Full-screen Modal -->
    <div class="fullscreen-modal" id="fullscreenModal">
        <div class="fullscreen-content">
            <img id="fullscreenImage" src="" alt="Full Screen View" class="fullscreen-image">
            <div class="fullscreen-controls">
                <div class="zoom-controls">
                    <button class="zoom-btn" id="zoomOut" title="Zoom Out">−</button>
                    <div class="zoom-level" id="zoomLevel">100%</div>
                    <button class="zoom-btn" id="zoomIn" title="Zoom In">+</button>
                </div>
                <button class="fullscreen-close" id="closeFullscreen">Close</button>
            </div>
        </div>
    </div>

    <script src="../js/diagnosis-system.js">
        
    </script>
    <script src="../js/sidebar.js"></script>
</body>
</html>