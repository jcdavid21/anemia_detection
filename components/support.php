<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support</title>
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="stylesheet" href="../styles/support.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include_once 'sidebar.php'; ?>

    <main>
        <div class="title-container">
            <h1>Help & Support</h1>
            <p>Need assistance? This page helps you on how to use the Hematology Diagnosis System.</p>
        </div>

        <!-- Purpose Section -->
        <div class="section-container">
            <h1>Purpose of the System
                <i class="fa-solid fa-bullseye"></i>
            </h1>
            <div class="purpose-content">
                <p>The Hematology Diagnosis System is designed to assist healthcare professionals in analyzing Complete Blood Count (CBC) results using advanced AI technology. This system helps in identifying blood disorders and provides accurate diagnostic insights for better patient care.</p>
            </div>
        </div>

        <!-- How to Use Section -->
        <div class="section-container">
            <h1>How to Use the System
                <i class="fa-solid fa-circle-info"></i>
                <div class="tooltip">
                    <p>Follow these steps to use the Hematology Diagnosis System</p>
                </div>
            </h1>
            <p class="hover-instruction">Hover over the icons to see detailed steps</p>
            
            <div class="grid-container">
                <div class="hover-container">
                    <div class="icon-wrapper">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span class="step-number">1</span>
                    </div>
                    <div class="step">
                        <h2>Upload CBC Result</h2>
                        <p>Click the "Upload" button and select a clear CBC result image from your device. Ensure the image is readable and contains all necessary parameters.</p>
                    </div>
                </div>

                <div class="hover-container">
                    <div class="icon-wrapper">
                        <i class="fa-solid fa-search"></i>
                        <span class="step-number">2</span>
                    </div>
                    <div class="step">
                        <h2>Analyze Image</h2>
                        <p>Once the image is uploaded, click the "Analyze" button. The AI system will process the CBC results and extract key parameters for diagnosis.</p>
                    </div>
                </div>

                <div class="hover-container">
                    <div class="icon-wrapper">
                        <i class="fa-solid fa-chart-line"></i>
                        <span class="step-number">3</span>
                    </div>
                    <div class="step">
                        <h2>View Results</h2>
                        <p>Review the comprehensive analysis results displayed on screen, including blood cell classifications and potential diagnostic indicators.</p>
                    </div>
                </div>

                <div class="hover-container">
                    <div class="icon-wrapper">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span class="step-number">4</span>
                    </div>
                    <div class="step">
                        <h2>Save Results</h2>
                        <p>Save the analysis results for future reference. You can download or store the diagnostic report in your preferred format.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blood Cell Classifications Section -->
        <div class="section-container">
            <h1>Blood Cell Classifications
                <i class="fa-solid fa-microscope"></i>
            </h1>
            <div class="classification-grid">
                <div class="classification-card">
                    <div class="classification-header normocytic">
                        <i class="fa-solid fa-circle"></i>
                        <h3>Normocytic</h3>
                    </div>
                    <div class="classification-content">
                        <p><strong>MCV Range:</strong> 80-100 fL</p>
                        <p><strong>Description:</strong> Normal-sized red blood cells. Indicates healthy red blood cell production and is typically seen in normal individuals or certain types of anemia.</p>
                    </div>
                </div>

                <div class="classification-card">
                    <div class="classification-header macrocytic">
                        <i class="fa-solid fa-circle"></i>
                        <h3>Macrocytic</h3>
                    </div>
                    <div class="classification-content">
                        <p><strong>MCV Range:</strong> >100 fL</p>
                        <p><strong>Description:</strong> Larger than normal red blood cells. Often associated with vitamin B12 or folate deficiency, liver disease, or certain medications.</p>
                    </div>
                </div>

                <div class="classification-card">
                    <div class="classification-header microcytic">
                        <i class="fa-solid fa-circle"></i>
                        <h3>Microcytic</h3>
                    </div>
                    <div class="classification-content">
                        <p><strong>MCV Range:</strong> < 80 fL</p>
                        <p><strong>Description:</strong> Smaller than normal red blood cells. Commonly seen in iron deficiency anemia, thalassemia, or chronic disease-related anemia.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../js/sidebar.js"></script>
</body>
</html>