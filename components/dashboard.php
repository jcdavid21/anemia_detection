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
    <style>
        /* Enhanced Styles */
        .main-content {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }

        .page-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .page-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .info-section {
            max-width: 1400px;
            margin: 0 auto 2rem auto;
            padding: 0 2rem;
        }

        .info-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-card {
            position: relative;
            height: 300px;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        /* Background Images */
        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: transform 0.5s ease;
            z-index: 1;
        }

        .info-card.card-anemia::before {
            background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), 
                              url('https://images.unsplash.com/photo-1584362917165-526a968579e8?w=800&q=80');
        }

        .info-card.card-symptoms::before {
            background-image: linear-gradient(rgba(231,76,60,0.3), rgba(231,76,60,0.5)), 
                              url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800&q=80');
        }

        .info-card.card-types::before {
            background-image: linear-gradient(rgba(243,156,18,0.3), rgba(243,156,18,0.5)), 
                              url('https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=800&q=80');
        }

        .info-card.card-treatment::before {
            background-image: linear-gradient(rgba(39,174,96,0.3), rgba(39,174,96,0.5)), 
                              url('https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=800&q=80');
        }

        .info-card:hover::before {
            transform: scale(1.1);
        }

        /* Overlay gradient */
        .info-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.9) 100%);
            z-index: 2;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .info-card:hover::after {
            opacity: 1;
        }

        /* Card Icon - Always visible */
        .info-card-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 4rem;
            z-index: 3;
            transition: all 0.5s ease;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.5));
        }

        .info-card:hover .info-card-icon {
            top: 20%;
            font-size: 3rem;
        }

        /* Card Title - Always visible at bottom */
        .info-card-title {
            position: absolute;
            bottom: 1.5rem;
            left: 1.5rem;
            right: 1.5rem;
            color: #ffffff;
            font-size: 1.5rem;
            font-weight: 700;
            z-index: 3;
            text-shadow: 0 2px 8px rgba(0,0,0,0.8);
            transition: all 0.3s ease;
        }

        .info-card:hover .info-card-title {
            bottom: auto;
            top: 30%;
            font-size: 1.3rem;
        }

        /* Card Text - Hidden, shows on hover */
        .info-card-text {
            position: absolute;
            bottom: 1.5rem;
            left: 1.5rem;
            right: 1.5rem;
            color: #ffffff;
            font-size: 0.95rem;
            line-height: 1.6;
            z-index: 3;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s ease 0.1s;
        }

        .info-card:hover .info-card-text {
            opacity: 1;
            transform: translateY(0);
        }

        /* About Section */
        .about-section {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .about-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .about-section h3 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .about-section p {
            color: #5a6c7d;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }

        .feature-list li {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #2c3e50;
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 3px solid #667eea;
        }

        .feature-list li:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .feature-list li::before {
            
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .info-cards-grid {
                grid-template-columns: 1fr;
            }

            .info-card {
                height: 250px;
            }

            .info-card-icon {
                font-size: 3rem;
            }

            .info-card:hover .info-card-icon {
                font-size: 2.5rem;
            }

            .about-section {
                padding: 1.5rem;
            }

            .feature-list {
                grid-template-columns: 1fr;
            }
        }

        /* System Info Cards */
        .system-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0.5rem 0;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Enhanced Upload Container */
        .upload-container {
            max-width: 1200px;
            margin: 0 auto 2rem auto;
            padding: 20px;
        }

        .upload-zone {
            background: white;
            border: 3px dashed #bdc3c7;
            border-radius: 15px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .upload-zone:hover {
            border-color: #3498db;
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.1);
        }

        .upload-zone.drag-over {
            border-color: #2ecc71;
            background: #e8f8f5;
        }

        .upload-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #95a5a6;
        }

        .upload-text {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .upload-subtext {
            color: #7f8c8d;
            font-size: 1rem;
        }

        /* About Section */
        .about-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .about-section h3 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .about-section p {
            color: #5a6c7d;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .feature-list li {
            padding: 0.75rem 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #2c3e50;
        }

        .feature-list li::before {
            content: "✓";
            background: #27ae60;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: bold;
        }

        /* Enhanced Analyze Button */
        .analyze-btn {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 1rem 3rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            margin: 10px auto;
        }

        .analyze-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .analyze-btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Results Container Enhancement */
        .results-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .results-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px 15px 0 0;
        }

        .results-content {
            background: white;
            padding: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .info-cards-grid {
                grid-template-columns: 1fr;
            }

            .upload-zone {
                padding: 2rem 1rem;
            }
        }

        /* Loading Animation Enhancement */
        .loading-container {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php include_once 'sidebar.php'; ?>

    <div class="main-content">
        <div style="display: flex; align-items: center; justify-content: center;">
            <div class="page-header">
                <h1>Anemia Diagnosis System</h1>
                <p>AI-Powered Complete Blood Count Analysis for Anemia Classification</p>
            </div>
        </div>

        <div class="info-section">
            <!-- Information Cards with Background Images -->
            <div class="info-cards-grid">
                <div class="info-card card-anemia">
                    <div class="info-card-icon" style="color: #d24434ff;">
                        <i class="fa-solid fa-droplet"></i>
                    </div>
                    <div class="info-card-title">What is Anemia?</div>
                    <div class="info-card-text">
                        Anemia is a condition where you lack enough healthy red blood cells to carry adequate oxygen to your body's tissues, leading to fatigue and weakness.
                    </div>
                </div>

                <div class="info-card card-symptoms">
                    <div class="info-card-icon" style="color: #d5695dff;">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <div class="info-card-title">Common Symptoms</div>
                    <div class="info-card-text">
                        Fatigue, weakness, pale skin, irregular heartbeat, shortness of breath, dizziness, chest pain, and cold hands and feet.
                    </div>
                </div>

                <div class="info-card card-types">
                    <div class="info-card-icon" style="color: #d2cf34ff;">
                        <i class="fa-solid fa-list"></i>
                    </div>
                    <div class="info-card-title">Types Detected</div>
                    <div class="info-card-text">
                        Our system can identify various types including Iron Deficiency, Vitamin B12 Deficiency, Folate Deficiency, and more.
                    </div>
                </div>

                <div class="info-card card-treatment">
                    <div class="info-card-icon" style="color: #61d234ff;">
                        <i class="fa-solid fa-pills"></i>
                    </div>
                    <div class="info-card-title">Treatment Options</div>
                    <div class="info-card-text">
                        Treatment depends on the type and may include supplements, dietary changes, medications, or addressing underlying conditions.
                    </div>
                </div>
            </div>

            <!-- About the System -->
            <div class="about-section">
                <h3>About Our AI System</h3>
                <p>
                    Our advanced hematology diagnosis system uses state-of-the-art deep learning algorithms to analyze Complete Blood Count (CBC) results and provide accurate anemia classifications. The system has been trained on thousands of verified medical cases to ensure reliability.
                </p>
                <ul class="feature-list">
                    <li>Instant AI-powered analysis</li>
                    <li>Multiple anemia type detection</li>
                    <li>Confidence level indicators</li>
                    <li>Detailed health risk assessment</li>
                    <li>Visual data representation</li>
                    <li>Secure result storage</li>
                </ul>
            </div>
        </div>

        <div class="upload-container">
            <div class="upload-zone" id="uploadZone">
                <div class="upload-icon">📋</div>
                <div class="upload-text">Drop your CBC result image here</div>
                <div class="upload-subtext">or click to browse files (PNG, JPG, JPEG)</div>
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
                    🔬 Analyze Image
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
                        <h3>💾 Save Results</h3>
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

    <script src="../js/diagnosis-system.js"></script>
    <script src="../js/sidebar.js"></script>
</body>

</html>