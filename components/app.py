import os
import re
from flask import Flask, render_template, request, jsonify
from werkzeug.utils import secure_filename

# Try to import OCR libraries, but make them optional
try:
    import pytesseract
    from PIL import Image
    import cv2
    import numpy as np
    OCR_AVAILABLE = True
except ImportError:
    OCR_AVAILABLE = False
    print("OCR libraries not available. Installing required packages...")

app = Flask(__name__)
app.secret_key = 'your-secret-key-here'
app.config['UPLOAD_FOLDER'] = 'uploads'
app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16MB max file size

# Create uploads directory if it doesn't exist
os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)

# Allowed file extensions
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff'}

def allowed_file(filename):
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def extract_text_from_image(image_path):
    """Enhanced text extraction with better preprocessing"""
    if not OCR_AVAILABLE:
        return "OCR libraries not available. Please install required packages."
    
    try:
        # Read image
        img = cv2.imread(image_path)
        
        # Convert to grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        # Apply different preprocessing techniques
        results = []
        
        # Method 1: Basic denoising + threshold
        denoised = cv2.fastNlMeansDenoising(gray)
        _, thresh1 = cv2.threshold(denoised, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
        text1 = pytesseract.image_to_string(thresh1, config='--oem 3 --psm 6')
        results.append(text1)
        
        # Method 2: Adaptive threshold
        adaptive = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2)
        text2 = pytesseract.image_to_string(adaptive, config='--oem 3 --psm 6')
        results.append(text2)
        
        # Method 3: Original image
        text3 = pytesseract.image_to_string(gray, config='--oem 3 --psm 6')
        results.append(text3)
        
        # Method 4: Different PSM mode for structured data
        text4 = pytesseract.image_to_string(gray, config='--oem 3 --psm 4')
        results.append(text4)
        
        # Return the longest result (usually most complete)
        best_text = max(results, key=len)
        return best_text
        
    except Exception as e:
        print(f"Error extracting text: {e}")
        return ""

def extract_cbc_values(text):
    """Enhanced CBC value extraction with better pattern matching"""
    cbc_values = {}
    
    print("=== OCR TEXT EXTRACTION ===")
    print(text)
    print("=== END OCR TEXT ===")
    
    # Clean text - normalize whitespace and remove special characters
    cleaned_text = re.sub(r'\s+', ' ', text)
    lines = text.split('\n')
    
    # Enhanced patterns - more flexible and comprehensive
    patterns = {
        'hemoglobin': [
            r'hemoglobin\s+(\d+\.?\d*)',
            r'hb\s+(\d+\.?\d*)',
            r'hgb\s+(\d+\.?\d*)',
            r'hemoglobin.*?(\d+\.\d+)',
            r'hemoglobin.*?(\d{2,3}\.?\d*)',  # For values like 152.30
        ],
        'hematocrit': [
            r'hematocrit\s+(\d+\.?\d*)',
            r'hct\s+(\d+\.?\d*)',
            r'pcv\s+(\d+\.?\d*)',
            r'hematocrit.*?(\d+\.\d+)',
            r'hematocrit.*?(0\.\d+)',  # For decimal format like 0.49
        ],
        'rbc': [
            r'rbc\s+count\s+(\d+\.?\d*)',
            r'rbc\s+(\d+\.?\d*)',
            r'red\s+blood\s+cell.*?(\d+\.?\d*)',
            r'rbc.*?(\d+\.\d+)',
        ],
        'mcv': [
            r'mcv\s+(\d+\.?\d*)',
            r'mcv.*?(\d+\.\d+)',
            r'mcv.*?(\d{2,3})',  # For values like 85.2
        ],
        'mch': [
            r'(?<!c)mch\s+(\d+\.?\d*)',  # Negative lookbehind to avoid MCHC
            r'(?<!c)mch.*?(\d+\.\d+)',
            r'(?<!c)mch.*?(\d{2}\.?\d*)',
        ],
        'mchc': [
            r'mchc\s+(\d+\.?\d*)',
            r'mchc.*?(\d+\.\d+)',
            r'mchc.*?(\d{2}\.?\d*)',
        ],
        'wbc': [
            r'wbc\s+count\s+(\d+\.?\d*)',
            r'wbc\s+(\d+\.?\d*)',
            r'white\s+blood\s+cell.*?(\d+\.?\d*)',
            r'wbc.*?(\d+\.\d+)',
        ],
        'platelet': [
            r'platelet\s+count\s+(\d+\.?\d*)',
            r'platelet\s+(\d+\.?\d*)',
            r'plt\s+(\d+\.?\d*)',
            r'platelet.*?(\d+)',
        ]
    }
    
    # Process each line
    for line in lines:
        line_clean = line.strip().lower()
        if not line_clean:
            continue
        
        print(f"Processing line: '{line_clean}'")
        
        # Check each parameter
        for param_name, param_patterns in patterns.items():
            if param_name in cbc_values:
                continue
            
            for pattern in param_patterns:
                matches = re.findall(pattern, line_clean, re.IGNORECASE)
                if matches:
                    try:
                        value = float(matches[0])
                        print(f"Found {param_name}: {value}")
                        
                        # Enhanced validation with proper ranges
                        if validate_cbc_value(param_name, value):
                            cbc_values[param_name] = value
                            print(f"✓ Accepted {param_name}: {value}")
                            break
                        else:
                            print(f"✗ Rejected {param_name}: {value} (out of range)")
                    except ValueError:
                        continue
    
    print(f"Final extracted values: {cbc_values}")
    return cbc_values

def validate_cbc_value(param_name, value):
    """Validate CBC values with appropriate ranges"""
    ranges = {
        'hemoglobin': (5, 250),    # g/dL (5-25) or mg/dL (50-250)
        'hematocrit': (0.15, 70),  # decimal (0.15-0.70) or percentage (15-70)
        'rbc': (2.0, 8.0),         # × 10¹²/L
        'mcv': (50, 150),          # fL
        'mch': (15, 50),           # pg
        'mchc': (25, 45),          # %
        'wbc': (1, 50),            # × 10³/µL
        'platelet': (50, 2000),    # × 10³/µL
    }
    
    if param_name in ranges:
        min_val, max_val = ranges[param_name]
        return min_val <= value <= max_val
    return False

def classify_anemia_type(cbc_values):
    """Improved anemia classification with better logic"""
    result = {
        'diagnosis': 'Unable to determine',
        'explanation': 'Insufficient data for classification',
        'values_used': cbc_values,
        'reference_ranges': {
            'hemoglobin_male': '14.0-18.0 g/dL',
            'hemoglobin_female': '12.0-16.0 g/dL',
            'hematocrit_male': '42-52%',
            'hematocrit_female': '37-47%',
            'mcv': '80-100 fL',
            'mch': '27-33 pg',
            'mchc': '32-36%',
            'wbc': '4.0-11.0 × 10³/µL',
            'rbc': '4.2-5.4 × 10¹²/L',
            'platelet': '150-450 × 10³/µL'
        }
    }
    
    # Check minimum requirements
    if not any(key in cbc_values for key in ['hemoglobin', 'hematocrit']):
        result['explanation'] = 'Missing both hemoglobin and hematocrit values'
        return result
    
    # Analyze hemoglobin and hematocrit
    has_anemia = False
    anemia_indicators = []
    
    # Hemoglobin analysis
    if 'hemoglobin' in cbc_values:
        hb = cbc_values['hemoglobin']
        
        # Determine unit and apply thresholds
        if hb >= 50:  # mg/dL
            threshold = 120  # mg/dL (female threshold for sensitivity)
            unit = 'mg/dL'
            normal_range = '120-180 mg/dL'
        else:  # g/dL
            threshold = 12.0  # g/dL (female threshold for sensitivity)
            unit = 'g/dL'
            normal_range = '12.0-18.0 g/dL'
        
        if hb < threshold:
            has_anemia = True
            anemia_indicators.append(f'Low hemoglobin: {hb} {unit} (normal: {normal_range})')
        else:
            anemia_indicators.append(f'Normal hemoglobin: {hb} {unit}')
    
    # Hematocrit analysis
    if 'hematocrit' in cbc_values:
        hct = cbc_values['hematocrit']
        
        if hct >= 1:  # Percentage
            threshold = 37.0
            unit = '%'
            normal_range = '37-52%'
        else:  # Decimal
            threshold = 0.37
            unit = ''
            normal_range = '0.37-0.52'
        
        if hct < threshold:
            has_anemia = True
            anemia_indicators.append(f'Low hematocrit: {hct}{unit} (normal: {normal_range})')
        else:
            anemia_indicators.append(f'Normal hematocrit: {hct}{unit}')
    
    # If no anemia detected but MCV is abnormal, still classify
    if 'mcv' in cbc_values:
        mcv = cbc_values['mcv']
        if not has_anemia and (mcv < 70 or mcv > 110):
            has_anemia = True
            anemia_indicators.append(f'Abnormal MCV suggests anemia: {mcv} fL')
    
    if not has_anemia:
        result['diagnosis'] = 'No Anemia'
        result['explanation'] = f'Values within normal range. {"; ".join(anemia_indicators)}'
        return result
    
    # Classify anemia type
    if 'mcv' not in cbc_values:
        result['diagnosis'] = 'Anemia (type undetermined)'
        result['explanation'] = f'Anemia detected but MCV missing. {"; ".join(anemia_indicators)}'
        return result
    
    mcv = cbc_values['mcv']
    
    if mcv < 80:
        result['diagnosis'] = 'Microcytic Anemia'
        causes = "Iron deficiency, thalassemia, chronic disease"
        result['explanation'] = f'Microcytic anemia (MCV: {mcv} fL, normal: 80-100). {"; ".join(anemia_indicators)}. Possible causes: {causes}'
    elif mcv > 100:
        result['diagnosis'] = 'Macrocytic Anemia'
        causes = "B12/folate deficiency, hypothyroidism, alcohol use"
        result['explanation'] = f'Macrocytic anemia (MCV: {mcv} fL, normal: 80-100). {"; ".join(anemia_indicators)}. Possible causes: {causes}'
    else:
        result['diagnosis'] = 'Normocytic Anemia'
        causes = "Chronic kidney disease, chronic inflammation, acute blood loss"
        result['explanation'] = f'Normocytic anemia (MCV: {mcv} fL, normal: 80-100). {"; ".join(anemia_indicators)}. Possible causes: {causes}'
    
    return result

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/upload', methods=['POST'])
def upload_file():
    try:
        if 'file' not in request.files:
            return jsonify({'error': 'No file uploaded'}), 400
        
        file = request.files['file']
        if file.filename == '':
            return jsonify({'error': 'No file selected'}), 400
        
        if not allowed_file(file.filename):
            return jsonify({'error': 'Invalid file type. Please upload an image file.'}), 400
        
        # Save uploaded file
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(filepath)
        
        # Extract text from image
        extracted_text = extract_text_from_image(filepath)
        
        if not extracted_text.strip():
            return jsonify({'error': 'Could not extract text from image. Please ensure the image is clear and contains CBC results.'}), 400
        
        # Extract CBC values
        cbc_values = extract_cbc_values(extracted_text)
        
        if not cbc_values:
            return jsonify({
                'error': 'Could not identify CBC values in the image. Please check that the image contains a valid CBC report.',
                'extracted_text': extracted_text,
                'debug': True
            }), 400
        
        # Classify anemia type
        classification = classify_anemia_type(cbc_values)
        
        # Clean up uploaded file
        try:
            os.remove(filepath)
        except:
            pass
        
        return jsonify({
            'success': True,
            'extracted_text': extracted_text,
            'cbc_values': cbc_values,
            'classification': classification,
            'ocr_available': OCR_AVAILABLE
        })
        
    except Exception as e:
        return jsonify({'error': f'An error occurred: {str(e)}'}), 500

if __name__ == '__main__':
    print("🩸 CBC Anemia Classifier Starting...")
    print(f"OCR Available: {OCR_AVAILABLE}")
    if not OCR_AVAILABLE:
        print("\n⚠️  OCR libraries not installed. Install with:")
        print("pip install opencv-python pytesseract pillow numpy")
        print("And install Tesseract OCR on your system.\n")
    
    print("Starting Flask server on port 8800...")
    print("Open your browser to: http://localhost:8800")
    
    try:
        app.run(debug=True, host='0.0.0.0', port=8800)
    except Exception as e:
        print(f"Error starting server: {e}")

# HTML Template (save as templates/index.html)
html_template = '''
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBC Anemia Classifier</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .main-content {
            padding: 40px;
        }

        .upload-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .upload-area {
            border: 3px dashed #4facfe;
            border-radius: 10px;
            padding: 40px;
            margin: 20px 0;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #667eea;
            background: #e3f2fd;
        }

        .upload-area.dragover {
            border-color: #667eea;
            background: #e3f2fd;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 3em;
            color: #4facfe;
            margin-bottom: 15px;
        }

        .file-input {
            display: none;
        }

        .upload-btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4facfe;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .results {
            display: none;
            margin-top: 30px;
        }

        .result-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 15px 0;
            border-left: 5px solid #4facfe;
        }

        .diagnosis {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .diagnosis.no-anemia {
            color: #28a745;
        }

        .diagnosis.anemia {
            color: #dc3545;
        }

        .explanation {
            font-size: 1.1em;
            line-height: 1.6;
            color: #666;
            margin-bottom: 20px;
        }

        .values-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .values-table th,
        .values-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .values-table th {
            background: #4facfe;
            color: white;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🩸 CBC Anemia Classifier</h1>
            <p>Upload your CBC result image for automatic anemia classification</p>
        </div>
        
        <div class="main-content">
            <div class="upload-section">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📋</div>
                    <h3>Upload CBC Result Image</h3>
                    <p>Drag and drop your CBC result image here or click to browse</p>
                    <input type="file" id="fileInput" class="file-input" accept="image/*">
                    <button class="upload-btn" onclick="document.getElementById('fileInput').click()">
                        Choose File
                    </button>
                </div>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Analyzing CBC results...</p>
            </div>

            <div id="error-message"></div>
            <div id="results" class="results"></div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const loading = document.getElementById('loading');
        const results = document.getElementById('results');
        const errorMessage = document.getElementById('error-message');

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        function handleFile(file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showError('Please select an image file.');
                return;
            }

            // Validate file size (max 16MB)
            if (file.size > 16 * 1024 * 1024) {
                showError('File size too large. Please select an image smaller than 16MB.');
                return;
            }

            uploadFile(file);
        }

        function uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);

            // Show loading
            loading.style.display = 'block';
            results.style.display = 'none';
            errorMessage.innerHTML = '';

            fetch('/upload', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                
                if (data.success) {
                    displayResults(data);
                } else {
                    showError(data.error || 'An error occurred while processing the image.');
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                showError('Network error: ' + error.message);
            });
        }

        function displayResults(data) {
            const classification = data.classification;
            const cbc_values = data.cbc_values;
            
            let diagnosisClass = 'anemia';
            if (classification.diagnosis === 'No Anemia') {
                diagnosisClass = 'no-anemia';
            }

            let valuesHtml = '';
            if (Object.keys(cbc_values).length > 0) {
                valuesHtml = `
                    <h4>Extracted CBC Values:</h4>
                    <table class="values-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Value</th>
                                <th>Reference Range</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                const referenceRanges = {
                    'hemoglobin': '12.0-18.0 g/dL',
                    'hematocrit': '37-52%',
                    'mcv': '80-100 fL',
                    'mch': '27-33 pg',
                    'mchc': '32-36%',
                    'rbc': '4.2-5.4 × 10¹²/L'
                };
                
                for (const [key, value] of Object.entries(cbc_values)) {
                    const displayName = key.toUpperCase();
                    const refRange = referenceRanges[key] || 'N/A';
                    valuesHtml += `
                        <tr>
                            <td>${displayName}</td>
                            <td>${value}</td>
                            <td>${refRange}</td>
                        </tr>
                    `;
                }
                
                valuesHtml += '</tbody></table>';
            }

            results.innerHTML = `
                <div class="result-card">
                    <div class="diagnosis ${diagnosisClass}">
                        Diagnosis: ${classification.diagnosis}
                    </div>
                    <div class="explanation">
                        ${classification.explanation}
                    </div>
                    ${valuesHtml}
                </div>
            `;
            
            results.style.display = 'block';
        }

        function showError(message) {
            errorMessage.innerHTML = `<div class="error">${message}</div>`;
        }
    </script>
</body>
</html>
'''

# Save the HTML template
os.makedirs('templates', exist_ok=True)
with open('templates/index.html', 'w') as f:
    f.write(html_template)

print("Flask app created successfully!")
print("\nTo run the application:")
print("1. Install required packages:")
print("   pip install flask opencv-python pytesseract pillow numpy")
print("2. Install Tesseract OCR:")
print("   - Windows: Download from https://github.com/UB-Mannheim/tesseract/wiki")
print("   - macOS: brew install tesseract")
print("   - Ubuntu/Debian: sudo apt-get install tesseract-ocr")
print("3. Run the app: python app.py")
print("4. Open browser to: http://localhost:8800")