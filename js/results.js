// Saved Results JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize page
    initializePage();
});

// Initialize page functionality
function initializePage() {
    // Add event listeners
    addEventListeners();
    
    // Initialize tooltips if needed
    initializeTooltips();
    
    // Auto-hide messages after 5 seconds
    autoHideMessages();
}

// Add event listeners
function addEventListeners() {
    // Modal close events
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeModal();
            closeDeleteModal();
        }
    });
    
    // Keyboard events
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeDeleteModal();
        }
    });
    
    // Form submission with loading state
    const filtersForm = document.getElementById('filtersForm');
    if (filtersForm) {
        filtersForm.addEventListener('submit', function(e) {
            showLoading();
        });
    }
}

// View result details
function viewResult(resultId) {
    const modal = document.getElementById('viewModal');
    const modalBody = document.getElementById('modalBody');
    
    // Show loading state
    modalBody.innerHTML = `
        <div class="loading-container" style="text-align: center; padding: 3rem;">
            <div class="loading"></div>
            <p style="margin-top: 1rem; color: #666;">Loading result details...</p>
        </div>
    `;
    
    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Fetch result details
    fetch(`../backend/users/get-result-details.php?id=${resultId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayResultDetails(data.result);
            } else {
                showError('Failed to load result details: ' + data.message);
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error fetching result details:', error);
            showError('Failed to load result details. Please try again.');
            closeModal();
        });
}

// Display result details in modal
function displayResultDetails(result) {
    const modalBody = document.getElementById('modalBody');
    
    const confidencePercent = result.confidence_score.replace('%', '');
    const analysisDate = new Date(result.analysis_date);
    
    modalBody.innerHTML = `
        <div class="result-details">
            <div class="detail-section">
                <h3>Patient Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Patient Name:</span>
                    <span class="detail-value">${result.patient_name}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Analysis Date:</span>
                    <span class="detail-value">${analysisDate.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</span>
                </div>
                ${result.notes ? `
                <div class="detail-row">
                    <span class="detail-label">Notes:</span>
                    <span class="detail-value">${result.notes}</span>
                </div>
                ` : ''}
            </div>
            
            <div class="detail-section">
                <h3>Diagnosis Results</h3>
                <div class="detail-row">
                    <span class="detail-label">Classification:</span>
                    <span class="detail-value">
                        <span class="classification-badge">${result.classification}</span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Confidence Score:</span>
                    <span class="detail-value">
                        <div class="confidence-detail">
                            <span>${result.confidence_score}</span>
                            <div class="confidence-bar">
                                <div class="confidence-fill" style="width: ${confidencePercent}%"></div>
                            </div>
                        </div>
                    </span>
                </div>
                ${result.explanation ? `
                <div class="detail-row">
                    <span class="detail-label">Explanation:</span>
                    <span class="detail-value">${result.explanation}</span>
                </div>
                ` : ''}
                ${result.health_risk ? `
                <div class="detail-row">
                    <span class="detail-label">Health Risk:</span>
                    <span class="detail-value">${result.health_risk}</span>
                </div>
                ` : ''}
            </div>
            
            ${result.image_filename ? `
            <div class="detail-section">
                <h3>Analyzed Image</h3>
                <div style="text-align: center; margin-top: 1rem;">
                    <img src="../backend/uploads/diagnosis_images/${result.image_filename}" 
                         alt="Analyzed CBC Image" 
                         class="image-preview"
                         onerror="this.style.display='none'">
                    <div style="margin-top: 0.5rem;">
                        <button class="btn-zoom" onclick="viewFullSizeImage('../backend/uploads/diagnosis_images/${result.image_filename}')" 
                                class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-zoom-in"></i> View Full Size
                        </button>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;
}

// Function to view full size image in a modal
function viewFullSizeImage(imageUrl) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1050;
        cursor: zoom-out;
    `;
    
    const img = document.createElement('img');
    img.src = imageUrl;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    `;
    
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '&times;';
    closeBtn.style.cssText = `
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        color: white;
        font-size: 30px;
        cursor: pointer;
    `;
    
    modal.appendChild(img);
    modal.appendChild(closeBtn);
    document.body.appendChild(modal);
    
    // Close modal on click
    modal.onclick = function() {
        document.body.removeChild(modal);
    };
    
    // Prevent clicks on the image from closing the modal
    img.onclick = function(e) {
        e.stopPropagation();
    };
}



// Delete result
function deleteResult(resultId) {
    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Show modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Set up confirm button
    confirmBtn.onclick = function() {
        performDelete(resultId);
    };
}

// Perform delete operation
function performDelete(resultId) {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const originalText = confirmBtn.textContent;
    
    // Show loading state
    confirmBtn.innerHTML = '<span class="loading"></span> Deleting...';
    confirmBtn.disabled = true;
    
    // Perform delete
    fetch('../backend/users/delete-result.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: resultId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Result deleted successfully');
            closeDeleteModal();
            
            // Remove row from table or reload page
            const row = document.querySelector(`button[onclick="deleteResult(${resultId})"]`).closest('tr');
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.remove();
                    updateResultCount();
                }, 300);
            }
        } else {
            showError('Failed to delete result: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting result:', error);
        showError('Failed to delete result. Please try again.');
    })
    .finally(() => {
        // Reset button
        confirmBtn.textContent = originalText;
        confirmBtn.disabled = false;
    });
}

// Close view modal
function closeModal() {
    const modal = document.getElementById('viewModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

// Close delete modal
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

// Show success message
function showSuccess(message) {
    showMessage(message, 'success');
}

// Show error message
function showError(message) {
    showMessage(message, 'error');
}

// Show warning message
function showWarning(message) {
    showMessage(message, 'warning');
}

// Show info message
function showInfo(message) {
    showMessage(message, 'info');
}

// Show message with type
function showMessage(message, type = 'success') {
    const container = document.getElementById('messageContainer');
    const messageId = 'message-' + Date.now();
    
    const messageDiv = document.createElement('div');
    messageDiv.id = messageId;
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    container.appendChild(messageDiv);
    messageDiv.style.opacity = '1';
    messageDiv.style.transform = 'translateY(0)';
    messageDiv.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    messageDiv.style.display = 'block';
    messageDiv.style.marginTop = '1rem';
    messageDiv.style.padding = '1rem';
    messageDiv.style.borderRadius = '0.5rem';
    messageDiv.style.backgroundColor = type === 'success' ? '#d4edda' :
                                        type === 'error' ? '#f8d7da' :
                                        type === 'warning' ? '#fff3cd' :
                                        '#cce5ff'; // info color
    messageDiv.style.color = type === 'success' ? '#155724' :
                            type === 'error' ? '#721c24' :
                            type === 'warning' ? '#856404' :
                            '#004085'; // info color
    messageDiv.style.border = `1px solid ${type === 'success' ? '#c3e6cb' :
                                  type === 'error' ? '#f5c6cb' :
                                  type === 'warning' ? '#ffeeba' :
                                  '#b8daff'}`; // info border color
    messageDiv.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
    messageDiv.style.zIndex = '1000';
    messageDiv.style.position = 'fixed';
    messageDiv.style.top = '1rem';
    messageDiv.style.right = '1rem';
    messageDiv.style.maxWidth = '300px';
    messageDiv.style.textAlign = 'center';
    messageDiv.style.cursor = 'pointer';
    messageDiv.onclick = function() {
        this.remove();
    };
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (messageDiv) {
            messageDiv.style.opacity = '0';
            messageDiv.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                messageDiv.remove();
            }, 300); // Match transition duration
        }
    }, 5000);
}
function autoHideMessages() {
    const messages = document.querySelectorAll('.message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                message.remove();
            }, 300); // Match transition duration
        }, 5000); // Auto-hide after 5 seconds
    });
}
function showLoading() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = `
        <div class="loading-spinner"></div>
        <p>Loading, please wait...</p>
    `;
    document.body.appendChild(loadingOverlay);
}
function hideLoading() {
    const loadingOverlay = document.querySelector('.loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}