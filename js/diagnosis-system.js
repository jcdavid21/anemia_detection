// Diagnosis System JavaScript Functionality

// Global variables
let selectedFile = null;
let confidenceChart = null;

// DOM elements
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const selectedFileDiv = document.getElementById('selectedFile');
const fileName = document.getElementById('fileName');
const analyzeBtn = document.getElementById('analyzeBtn');
const loadingContainer = document.getElementById('loadingContainer');
const resultsContainer = document.getElementById('resultsContainer');
const errorMessage = document.getElementById('errorMessage');

// Initialize diagnosis system
function initDiagnosisSystem() {
    setupUploadZone();
    setupFileInput();
    setupAnalyzeButton();
}

// Setup upload zone interactions
function setupUploadZone() {
    // Click to upload
    uploadZone.addEventListener('click', () => fileInput.click());

    // Drag and drop functionality
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');

        if (e.dataTransfer.files.length > 0) {
            handleFileSelection(e.dataTransfer.files[0]);
        }
    });
}

// Setup file input
function setupFileInput() {
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelection(e.target.files[0]);
        }
    });
}

// Setup analyze button
function setupAnalyzeButton() {
    analyzeBtn.addEventListener('click', analyzeImage);
    //scroll to bottom of the page when analyze button is clicked
    analyzeBtn.addEventListener('click', () => {
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: 'smooth'
        });
    });
}

// Handle file selection
function handleFileSelection(file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showError('Please select a valid image file.');
        return;
    }

    // Validate file size (10MB limit)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
        showError('File size must be less than 10MB.');
        return;
    }

    selectedFile = file;
    fileName.textContent = file.name;
    selectedFileDiv.classList.add('show');
    analyzeBtn.disabled = false;
    hideError();
}

// Analyze image function
function analyzeImage() {
    if (!selectedFile) {
        showError('Please select a file first');
        return;
    }

    // Show loading state
    showLoading();
    hideError();

    // Convert file to base64
    const reader = new FileReader();
    reader.onload = function (e) {
        const imageData = e.target.result;

        // Send to backend
        sendImageToBackend(imageData);
    };

    reader.onerror = function () {
        hideLoading();
        showError('Error reading the file. Please try again.');
    };

    reader.readAsDataURL(selectedFile);
}

// Send image to backend
function sendImageToBackend(imageData) {
    fetch('http://localhost:8800/predict_anemia', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            image: imageData
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoading();

            if (data.error) {
                showError(data.error);
                return;
            }

            displayResults(data);
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showError('An error occurred while analyzing the image. Please check your connection and try again.');
        });
}

// Display analysis results
function displayResults(data) {
    try {
        // Update classification
        const classificationElement = document.getElementById('classificationValue');
        if (classificationElement) {
            classificationElement.textContent = data.classification || 'Unknown';
        }

        // Parse and update confidence
        const confidenceScore = data.confidence_score || '0%';
        const confidencePercent = parseInt(confidenceScore.replace('%', '')) || 0;

        const confidenceText = document.getElementById('confidenceText');
        if (confidenceText) {
            confidenceText.textContent = confidenceScore;
        }

        // Update confidence bar
        updateConfidenceBar(confidencePercent);

        // Update explanation
        const explanationElement = document.getElementById('explanationText');
        if (explanationElement) {
            explanationElement.textContent = data.explanation || 'No explanation available.';
        }

        // Update recommendations
        updateRecommendations(data.healthrisk);

        // Create confidence chart
        createConfidenceChart(confidencePercent);

        // Show results
        resultsContainer.classList.add('show');

    } catch (error) {
        console.error('Error displaying results:', error);
        showError('Error displaying results. Please try again.');
    }
}

// Update confidence bar
function updateConfidenceBar(confidencePercent) {
    const confidenceFill = document.getElementById('confidenceFill');
    if (confidenceFill) {
        confidenceFill.style.width = confidencePercent + '%';
    }
}

// Update recommendations list
function updateRecommendations(recommendations) {
    const recommendationsList = document.getElementById('recommendationsList');
    if (!recommendationsList) return;

    recommendationsList.innerHTML = '';

    if (recommendations) {
        // Split recommendations by periods or bullet points and create list items
        const recommendationItems = recommendations
            .split(/[.•]/)
            .map(item => item.trim())
            .filter(item => item.length > 10); // Filter out very short items

        if (recommendationItems.length > 0) {
            recommendationItems.forEach(rec => {
                const li = document.createElement('li');
                li.textContent = rec;
                recommendationsList.appendChild(li);
            });
        } else {
            // If splitting didn't work, use the full text as one item
            const li = document.createElement('li');
            li.textContent = recommendations;
            recommendationsList.appendChild(li);
        }
    } else {
        const li = document.createElement('li');
        li.textContent = 'No recommendations available';
        recommendationsList.appendChild(li);
    }
}

// Create confidence chart
function createConfidenceChart(confidence) {
    const ctx = document.getElementById('confidenceChart');
    if (!ctx) return;

    const context = ctx.getContext('2d');

    // Destroy existing chart if it exists
    if (confidenceChart) {
        confidenceChart.destroy();
        confidenceChart = null;
    }

    // Determine color based on confidence level
    let confidenceColor = '#F44336'; // Red for low confidence
    if (confidence >= 70) {
        confidenceColor = '#4CAF50'; // Green for high confidence
    } else if (confidence >= 50) {
        confidenceColor = '#FF9800'; // Orange for medium confidence
    }

    confidenceChart = new Chart(context, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [confidence, 100 - confidence],
                backgroundColor: [confidenceColor, '#E0E0E0'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            },
            animation: {
                animateRotate: true,
                duration: 1000
            }
        },
        plugins: [{
            beforeDraw: function (chart) {
                const { width, height, ctx } = chart;
                ctx.restore();

                const fontSize = Math.min(width, height) / 8;
                ctx.font = `bold ${fontSize}px Poppins, sans-serif`;
                ctx.textBaseline = "middle";
                ctx.fillStyle = "#000000";

                const text = confidence + "%";
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2;

                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });
}

// Show loading state with two phases
function showLoading() {
    loadingContainer.classList.add('show');
    resultsContainer.classList.remove('show');
    
    // First phase: Extracting text
    const loadingText = document.querySelector('.loading-text');
    const loadingSubtext = document.querySelector('.loading-subtext');
    
    loadingText.textContent = 'Extracting text from image...';
    loadingSubtext.textContent = 'Processing the uploaded image to extract text data';
    
    // After 3 seconds, switch to analyzing phase
    setTimeout(() => {
        loadingText.textContent = 'Analyzing your CBC result...';
        loadingSubtext.textContent = 'This may take a few moments while our AI processes the image';
    }, 5000);
}

// Hide loading state
function hideLoading() {
    loadingContainer.classList.remove('show');
}

// Show error message
function showError(message) {
    errorMessage.textContent = message;
    errorMessage.classList.add('show');
}

// Hide error message
function hideError() {
    errorMessage.classList.remove('show');
}

// Reset form
function resetForm() {
    selectedFile = null;
    fileInput.value = '';
    fileName.textContent = '';
    selectedFileDiv.classList.remove('show');
    analyzeBtn.disabled = true;
    hideError();
    hideLoading();
    resultsContainer.classList.remove('show');

    if (confidenceChart) {
        confidenceChart.destroy();
        confidenceChart = null;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    initDiagnosisSystem();
});

// Export functions for global access
window.diagnosisSystem = {
    resetForm,
    analyzeImage,
    showError,
    hideError
};

// Additional functionality for image preview and saving
let currentAnalysisData = null;

// Add these to your existing DOM elements
const imagePreview = document.getElementById('imagePreview');
const previewImage = document.getElementById('previewImage');
const saveBtn = document.getElementById('saveBtn');
const saveMessage = document.getElementById('saveMessage');
const patientNameInput = document.getElementById('patientName');
const notesInput = document.getElementById('notes');

// Update the existing handleFileSelection function
function handleFileSelection(file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
        showError('Please select a valid image file.');
        return;
    }

    // Validate file size (10MB limit)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
        showError('File size must be less than 10MB.');
        return;
    }

    selectedFile = file;
    fileName.textContent = file.name;
    selectedFileDiv.classList.add('show');
    analyzeBtn.disabled = false;
    hideError();

    // Show image preview
    displayImagePreview(file);
}

// New function to display image preview
function displayImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function (e) {
        previewImage.src = e.target.result;
        imagePreview.classList.add('show');
    };
    reader.readAsDataURL(file);
}

// Update the existing displayResults function
function displayResults(data) {
    try {
        // Store analysis data for saving
        currentAnalysisData = data;

        // Update classification
        const classificationElement = document.getElementById('classificationValue');
        if (classificationElement) {
            classificationElement.textContent = data.classification || 'Unknown';
        }

        // Parse and update confidence
        const confidenceScore = data.confidence_score || '0%';
        const confidencePercent = parseInt(confidenceScore.replace('%', '')) || 0;

        const confidenceText = document.getElementById('confidenceText');
        if (confidenceText) {
            confidenceText.textContent = confidenceScore;
        }

        // Update confidence bar
        updateConfidenceBar(confidencePercent);

        // Update explanation
        const explanationElement = document.getElementById('explanationText');
        if (explanationElement) {
            explanationElement.textContent = data.explanation || 'No explanation available.';
        }

        // Update recommendations
        updateRecommendations(data.healthrisk);

        // Create confidence chart
        createConfidenceChart(confidencePercent);

        // Show results
        resultsContainer.classList.add('show');

        // Setup save button
        setupSaveButton();

    } catch (error) {
        console.error('Error displaying results:', error);
        showError('Error displaying results. Please try again.');
    }
}

// New function to setup save button
function setupSaveButton() {
    saveBtn.addEventListener('click', saveResultsToDatabase);
}

// New function to save results to database
function saveResultsToDatabase() {
    if (!currentAnalysisData || !selectedFile) {
        showError('No analysis data to save.');
        return;
    }

    // Disable save button during processing
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    // Convert image to base64
    const reader = new FileReader();
    reader.onload = function (e) {
        const imageData = e.target.result;

        // Prepare data for database
        const saveData = {
            patient_name: patientNameInput.value.trim() || 'Anonymous',
            notes: notesInput.value.trim() || '',
            image_data: imageData,
            classification: currentAnalysisData.classification,
            confidence_score: currentAnalysisData.confidence_score,
            explanation: currentAnalysisData.explanation,
            health_risk: currentAnalysisData.healthrisk,
            analysis_date: new Date().toISOString()
        };

        // Send to PHP backend
        fetch('../backend/users/result.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(saveData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSaveSuccess();
                    // Clear form fields
                    patientNameInput.value = '';
                    notesInput.value = '';
                } else {
                    showError(data.message || 'Failed to save results.');
                }
            })
            .catch(error => {
                console.error('Error saving:', error);
                showError('An error occurred while saving. Please try again.');
            })
            .finally(() => {
                // Re-enable save button
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save to Database';
            });
    };

    reader.readAsDataURL(selectedFile);
}

// New function to show save success message
function showSaveSuccess() {
    saveMessage.classList.add('show');
    setTimeout(() => {
        saveMessage.classList.remove('show');
    }, 3000);
}

// Update the existing resetForm function
function resetForm() {
    selectedFile = null;
    currentAnalysisData = null;
    fileInput.value = '';
    fileName.textContent = '';
    selectedFileDiv.classList.remove('show');
    imagePreview.classList.remove('show');
    analyzeBtn.disabled = true;
    hideError();
    hideLoading();
    resultsContainer.classList.remove('show');

    // Clear form inputs
    patientNameInput.value = '';
    notesInput.value = '';

    if (confidenceChart) {
        confidenceChart.destroy();
        confidenceChart = null;
    }
}

// Full-screen functionality
let currentZoomLevel = 1;
const zoomStep = 0.25;
const minZoom = 0.5;
const maxZoom = 3;

// DOM elements for full-screen functionality
const previewContainer = document.getElementById('previewContainer');
const fullscreenModal = document.getElementById('fullscreenModal');
const fullscreenImage = document.getElementById('fullscreenImage');
const closeFullscreen = document.getElementById('closeFullscreen');
const zoomIn = document.getElementById('zoomIn');
const zoomOut = document.getElementById('zoomOut');
const zoomLevel = document.getElementById('zoomLevel');

// Open full-screen view
function openFullscreen() {
    const previewImg = document.getElementById('previewImage');
    if (previewImg.src) {
        fullscreenImage.src = previewImg.src;
        fullscreenModal.classList.add('show');
        document.body.style.overflow = 'hidden';
        currentZoomLevel = 1;
        updateZoom();
    }
}

// Close full-screen view
function closeFullscreenView() {
    fullscreenModal.classList.remove('show');
    document.body.style.overflow = 'auto';
    currentZoomLevel = 1;
}

// Update zoom level
function updateZoom() {
    fullscreenImage.style.transform = `scale(${currentZoomLevel})`;
    zoomLevel.textContent = Math.round(currentZoomLevel * 100) + '%';

    // Update button states
    zoomOut.disabled = currentZoomLevel <= minZoom;
    zoomIn.disabled = currentZoomLevel >= maxZoom;
}

// Zoom in
function zoomInImage() {
    if (currentZoomLevel < maxZoom) {
        currentZoomLevel += zoomStep;
        updateZoom();
    }
}

// Zoom out
function zoomOutImage() {
    if (currentZoomLevel > minZoom) {
        currentZoomLevel -= zoomStep;
        updateZoom();
    }
}

// Event listeners
previewContainer.addEventListener('click', openFullscreen);
closeFullscreen.addEventListener('click', closeFullscreenView);
zoomIn.addEventListener('click', zoomInImage);
zoomOut.addEventListener('click', zoomOutImage);

// Close on background click
fullscreenModal.addEventListener('click', function (e) {
    if (e.target === fullscreenModal) {
        closeFullscreenView();
    }
});

// Keyboard controls
document.addEventListener('keydown', function (e) {
    if (fullscreenModal.classList.contains('show')) {
        switch (e.key) {
            case 'Escape':
                closeFullscreenView();
                break;
            case '+':
            case '=':
                e.preventDefault();
                zoomInImage();
                break;
            case '-':
                e.preventDefault();
                zoomOutImage();
                break;
        }
    }
});