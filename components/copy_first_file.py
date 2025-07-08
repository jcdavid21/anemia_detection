from flask import Flask, request, jsonify
import os
import base64
from dotenv import load_dotenv
import tempfile
from google import generativeai as genai
from flask_cors import CORS
import json
import cv2
import pytesseract
import re
import numpy as np
from PIL import Image

load_dotenv()

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

API_KEY = os.getenv("GOOGLE_API_KEY", "AIzaSyCzVwiwU-DBYVlgcXOgg9-4NUpd9Hjw5iA")
genai.configure(api_key=API_KEY)

class CBCExtractor:
    def __init__(self):
        # Updated patterns to be more flexible and handle different formats
        self.cbc_parameters = {
            'Hemoglobin': [
                r'Hemoglobin\s+(\d+\.?\d*)',
                r'Hb\s+(\d+\.?\d*)',
                r'HGB\s+(\d+\.?\d*)'
            ],
            'Hematocrit': [
                r'Hematocrit\s+(\d+\.?\d*)',
                r'HCT\s+(\d+\.?\d*)',
                r'Hct\s+(\d+\.?\d*)'
            ],
            'RBC Count': [
                r'RBC Count\s+(\d+\.?\d*)',
                r'RBC\s+(\d+\.?\d*)',
                r'Red Blood Cell\s+(\d+\.?\d*)'
            ],
            'MCV': [
                r'MCV\s+(\d+\.?\d*)',
                r'Mean Corpuscular Volume\s+(\d+\.?\d*)'
            ],
            'MCH': [
                r'MCH\s+(\d+\.?\d*)',
                r'Mean Corpuscular Hemoglobin\s+(\d+\.?\d*)'
            ],
            'MCHC': [
                r'MCHC\s+(\d+\.?\d*)',
                r'Mean Corpuscular Hemoglobin Concentration\s+(\d+\.?\d*)'
            ],
            'RDW': [
                r'RDW\s+(\d+\.?\d*)',
                r'Red Cell Distribution Width\s+(\d+\.?\d*)'
            ],
            'WBC Count': [
                r'WBC Count\s+(\d+\.?\d*)',
                r'WBC\s+(\d+\.?\d*)',
                r'White Blood Cell\s+(\d+\.?\d*)'
            ],
            'Platelet Count': [
                r'Platelet Count\s+(\d+\.?\d*)',
                r'PLT\s+(\d+\.?\d*)',
                r'Platelets\s+(\d+\.?\d*)'
            ],
            'Neutrophils': [
                r'Neutrophils\s+(\d+\.?\d*)',
                r'Neutropils\s+(\d+\.?\d*)',  # Common OCR error
                r'NEUT\s+(\d+\.?\d*)',
                r'Neutros\s+(\d+\.?\d*)'
            ],
            'Lymphocytes': [
                r'Lymphocytes\s+(\d+\.?\d*)',
                r'LYMPH\s+(\d+\.?\d*)',
                r'Lymphs\s+(\d+\.?\d*)'
            ],
            'Eosinophils': [
                r'Eosinophils\s+(\d+\.?\d*)',
                r'Eosinopils\s+(\d+\.?\d*)',  # Common OCR error
                r'EOS\s+(\d+\.?\d*)',
                r'Eos\s+(\d+\.?\d*)'
            ],
            'Monocytes': [
                r'Monocytes\s+(\d+\.?\d*)',
                r'MONO\s+(\d+\.?\d*)',
                r'Monos\s+(\d+\.?\d*)'
            ]
        }
    
    def preprocess_image(self, image_path):
        """Enhanced image preprocessing for better OCR results"""
        try:
            img = cv2.imread(image_path)
            if img is None:
                pil_img = Image.open(image_path)
                img = cv2.cvtColor(np.array(pil_img), cv2.COLOR_RGB2BGR)
            
            # Convert to grayscale
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            
            # Apply different preprocessing techniques
            # Method 1: Basic thresholding
            _, thresh1 = cv2.threshold(gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
            
            # Method 2: Adaptive thresholding
            thresh2 = cv2.adaptiveThreshold(gray, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, 
                                          cv2.THRESH_BINARY, 11, 2)
            
            # Method 3: Morphological operations
            kernel = np.ones((2,2), np.uint8)
            thresh3 = cv2.morphologyEx(thresh1, cv2.MORPH_CLOSE, kernel)
            thresh3 = cv2.morphologyEx(thresh3, cv2.MORPH_OPEN, kernel)
            
            # Method 4: Gaussian blur + threshold
            blurred = cv2.GaussianBlur(gray, (5, 5), 0)
            _, thresh4 = cv2.threshold(blurred, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
            
            # Method 5: Enhanced contrast
            clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8,8))
            enhanced = clahe.apply(gray)
            _, thresh5 = cv2.threshold(enhanced, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
            
            return [gray, thresh1, thresh2, thresh3, thresh4, thresh5]
            
        except Exception as e:
            print(f"Error preprocessing image: {e}")
            return [None]
    
    def extract_text_from_image(self, image_path):
        """Extract text using multiple OCR configurations with better settings"""
        try:
            processed_images = self.preprocess_image(image_path)
            
            # Multiple OCR configurations optimized for medical reports
            ocr_configs = [
                r'--oem 3 --psm 6 -c tessedit_char_whitelist=0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.%/',
                r'--oem 3 --psm 4 -c tessedit_char_whitelist=0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz.%/',
                r'--oem 3 --psm 3',  # Fully automatic page segmentation
                r'--oem 3 --psm 11', # Sparse text
                r'--oem 3 --psm 12', # Sparse text with OSD
                r'--oem 3 --psm 13'  # Raw line
            ]
            
            all_texts = []
            
            # Try different preprocessing methods and OCR configs
            for img in processed_images:
                if img is not None:
                    for config in ocr_configs:
                        try:
                            text = pytesseract.image_to_string(img, config=config)
                            if text.strip():
                                all_texts.append(text)
                        except:
                            continue
            
            # Also try with original image
            try:
                original_text = pytesseract.image_to_string(image_path)
                if original_text.strip():
                    all_texts.append(original_text)
            except:
                pass
            
            return all_texts
            
        except Exception as e:
            print(f"Error extracting text: {e}")
            return [""]
    
    def parse_cbc_results(self, texts):
        """Parse CBC results from multiple text extractions with improved accuracy"""
        results = {}
        
        for text in texts:
            if not text.strip():
                continue
                
            # Clean the text
            cleaned_text = re.sub(r'\s+', ' ', text)
            
            # Try regex patterns first
            for parameter, patterns in self.cbc_parameters.items():
                if parameter in results:  # Skip if already found
                    continue
                    
                for pattern in patterns:
                    match = re.search(pattern, cleaned_text, re.IGNORECASE)
                    if match:
                        try:
                            value = float(match.group(1))
                            # Apply parameter-specific validation and correction
                            corrected_value = self.correct_ocr_value(parameter, value, cleaned_text)
                            if corrected_value is not None:
                                results[parameter] = corrected_value
                                break
                        except ValueError:
                            continue
            
            # Enhanced flexible parsing with better line-by-line analysis
            flexible_results = self.enhanced_flexible_parsing(cleaned_text)
            
            # Merge results, prioritizing regex matches
            for param, value in flexible_results.items():
                if param not in results:
                    results[param] = value
        
        # Final post-processing to ensure consistency
        results = self.final_validation_and_correction(results)
        
        return results
    
    def correct_ocr_value(self, parameter, value, context):
        """Correct common OCR errors for specific parameters"""
        
        # Fix WBC Count issues (common OCR error: 63 instead of 6.3)
        if parameter == 'WBC Count':
            if value > 30:  # Unrealistically high WBC
                # Check if it should be divided by 10
                if 2.0 <= value/10 <= 20.0:
                    return value/10
                # Check if it's a decimal point issue
                str_value = str(value)
                if len(str_value) >= 2:
                    # Try inserting decimal point at different positions
                    for i in range(1, len(str_value)):
                        try:
                            new_val = float(str_value[:i] + '.' + str_value[i:])
                            if 2.0 <= new_val <= 20.0:
                                return new_val
                        except:
                            continue
            elif 2.0 <= value <= 20.0:
                return value
            return None
        
        # Fix differential count percentages
        elif parameter in ['Neutrophils', 'Lymphocytes', 'Eosinophils', 'Monocytes']:
            # If value looks like a percentage (0.1-1.0), check if it should be the actual percentage
            if 0.01 <= value <= 1.0:
                # Look for the actual percentage value in context
                param_variations = {
                    'Neutrophils': ['neutrophils', 'neutropils', 'neut'],
                    'Lymphocytes': ['lymphocytes', 'lymph'],
                    'Eosinophils': ['eosinophils', 'eosinopils', 'eos'],
                    'Monocytes': ['monocytes', 'mono']
                }
                
                for variation in param_variations.get(parameter, []):
                    # Look for the line containing this parameter
                    lines = context.lower().split('\n')
                    for line in lines:
                        if variation in line:
                            # Extract all numbers from this line
                            numbers = re.findall(r'\d+\.?\d*', line)
                            for num_str in numbers:
                                try:
                                    num = float(num_str)
                                    # Check if this looks like a percentage value
                                    if parameter == 'Neutrophils' and 40 <= num <= 80:
                                        return num / 100  # Convert to decimal
                                    elif parameter == 'Lymphocytes' and 15 <= num <= 50:
                                        return num / 100
                                    elif parameter == 'Eosinophils' and 0 <= num <= 10:
                                        return num / 100
                                    elif parameter == 'Monocytes' and 0 <= num <= 15:
                                        return num / 100
                                except ValueError:
                                    continue
                return value  # Return original if no better value found
            
            # If value is already in percentage form, convert to decimal
            elif value > 1:
                if parameter == 'Neutrophils' and 40 <= value <= 80:
                    return value / 100
                elif parameter == 'Lymphocytes' and 15 <= value <= 50:
                    return value / 100
                elif parameter == 'Eosinophils' and 0 <= value <= 10:
                    return value / 100
                elif parameter == 'Monocytes' and 0 <= value <= 15:
                    return value / 100
            
            return value
        
        # Apply existing validation for other parameters
        elif self.is_valid_cbc_value(parameter, value):
            return value
        
        return None
    
    def enhanced_flexible_parsing(self, text):
        """Enhanced flexible parsing with better value extraction and line analysis"""
        results = {}
        lines = text.split('\n')
        
        # Define possible parameter names and their variations
        param_variations = {
            'Hemoglobin': ['hemoglobin', 'hb', 'hgb', 'haemoglobin'],
            'Hematocrit': ['hematocrit', 'hct', 'haematocrit'],
            'RBC Count': ['rbc count', 'rbc', 'red blood cell', 'red blood cells'],
            'MCV': ['mcv', 'mean corpuscular volume'],
            'MCH': ['mch', 'mean corpuscular hemoglobin'],
            'MCHC': ['mchc', 'mean corpuscular hemoglobin concentration'],
            'RDW': ['rdw', 'red cell distribution width', 'red distribution width'],
            'WBC Count': ['wbc count', 'wbc', 'white blood cell', 'white blood cells'],
            'Platelet Count': ['platelet count', 'plt', 'platelets', 'platelet'],
            'Neutrophils': ['neutrophils', 'neutropils', 'neut', 'neutros', 'neutrophil'],
            'Lymphocytes': ['lymphocytes', 'lymph', 'lymphs', 'lymphocyte'],
            'Eosinophils': ['eosinophils', 'eosinopils', 'eos', 'eosinophil'],
            'Monocytes': ['monocytes', 'mono', 'monos', 'monocyte']
        }
        
        for line in lines:
            original_line = line.strip()
            line_lower = line.strip().lower()
            if not line_lower:
                continue
            
            # Look for parameter names
            for param, variations in param_variations.items():
                if param in results:  # Skip if already found
                    continue
                    
                for variation in variations:
                    if variation in line_lower:
                        # Extract all numbers from the line, preserving their position
                        number_matches = list(re.finditer(r'\d+\.?\d*', original_line))
                        
                        if number_matches:
                            # For differential counts, look for percentage values
                            if param in ['Neutrophils', 'Lymphocytes', 'Eosinophils', 'Monocytes']:
                                # Look for the first reasonable percentage value
                                for match in number_matches:
                                    try:
                                        num = float(match.group())
                                        
                                        # Check if this is a percentage value that should be converted
                                        if param == 'Neutrophils' and (0.4 <= num <= 0.8 or 40 <= num <= 80):
                                            results[param] = num/100 if num > 1 else num
                                            break
                                        elif param == 'Lymphocytes' and (0.15 <= num <= 0.5 or 15 <= num <= 50):
                                            results[param] = num/100 if num > 1 else num
                                            break
                                        elif param == 'Eosinophils' and (0 <= num <= 0.1 or 0 <= num <= 10):
                                            results[param] = num/100 if num > 1 else num
                                            break
                                        elif param == 'Monocytes' and (0 <= num <= 0.15 or 0 <= num <= 15):
                                            results[param] = num/100 if num > 1 else num
                                            break
                                    except ValueError:
                                        continue
                            else:
                                # For other parameters, take the first valid number
                                for match in number_matches:
                                    try:
                                        num = float(match.group())
                                        corrected_value = self.correct_ocr_value(param, num, original_line)
                                        if corrected_value is not None:
                                            results[param] = corrected_value
                                            break
                                    except ValueError:
                                        continue
                        break
        
        return results
    
    def is_valid_cbc_value(self, parameter, value):
        """Validate if a value is reasonable for a CBC parameter"""
        value_ranges = {
            'Hemoglobin': (80, 200),   # g/L - expanded range
            'Hematocrit': (0.25, 0.6), # L/L (as decimal)
            'RBC Count': (3.0, 6.5),   # x10^12/L
            'MCV': (70, 110),          # fL
            'MCH': (22, 35),           # pg
            'MCHC': (300, 370),        # g/L
            'RDW': (11, 16),           # %
            'WBC Count': (3.0, 15.0),  # x10^9/L - more restrictive
            'Platelet Count': (150, 500), # x10^9/L
            'Neutrophils': (0.40, 0.80),  # proportion (40-80%)
            'Lymphocytes': (0.15, 0.50),  # proportion (15-50%)
            'Eosinophils': (0.00, 0.10),  # proportion (0-10%)
            'Monocytes': (0.00, 0.15)     # proportion (0-15%)
        }
        
        if parameter in value_ranges:
            min_val, max_val = value_ranges[parameter]
            return min_val <= value <= max_val
        
        return True  # If no range defined, accept the value
    
    def final_validation_and_correction(self, results):
        """Final validation and correction of extracted values"""
        corrected = {}
        
        for param, value in results.items():
            # Additional corrections based on cross-parameter validation
            if param == 'Hematocrit' and value > 1:
                # Convert percentage to decimal if needed
                corrected[param] = value / 100 if value <= 100 else value
            elif param in ['Neutrophils', 'Lymphocytes', 'Eosinophils', 'Monocytes']:
                # Ensure differential counts are in decimal form and sum reasonably
                if value > 1:
                    corrected[param] = value / 100
                else:
                    corrected[param] = value
            else:
                corrected[param] = value
        
        # Validate that differential counts sum to approximately 1.0
        diff_params = ['Neutrophils', 'Lymphocytes', 'Eosinophils', 'Monocytes']
        diff_values = [corrected.get(p, 0) for p in diff_params if p in corrected]
        
        if len(diff_values) >= 2:  # If we have at least 2 differential counts
            total = sum(diff_values)
            # If sum is way off from 1.0, there might be an extraction error
            if total > 1.5 or total < 0.5:
                print(f"Warning: Differential count sum is {total}, which seems incorrect")
        
        return corrected
    
    def extract_cbc_data(self, image_path):
        """Main method to extract CBC data from image"""
        texts = self.extract_text_from_image(image_path)
        results = self.parse_cbc_results(texts)
        
        # Combine all extracted texts for debugging
        combined_text = "\n".join(texts)
        
        return {
            'extracted_text': combined_text,
            'cbc_values': results,
            'is_valid_cbc': len(results) >= 3  # Require at least 3 parameters
        }

@app.route("/predict_anemia", methods=["POST"])
def predict_anemia():
    try:
        image_data = request.json.get("image")
        if not image_data:
            return jsonify({"error": "No image data provided"}), 400
        
        # Decode base64 image
        base64_image = image_data.split(",")[1]
        binary_image = base64.b64decode(base64_image)

        # Create temporary file
        with tempfile.NamedTemporaryFile(delete=False, suffix=".png") as temp_file:
            temp_file.write(binary_image)
            temp_file_path = temp_file.name

        try:
            # Extract CBC data using improved OCR
            extractor = CBCExtractor()
            cbc_data = extractor.extract_cbc_data(temp_file_path)
            
            print("Extracted CBC values:", cbc_data['cbc_values'])
            
            # Check if valid CBC data was extracted
            if not cbc_data['is_valid_cbc']:
                return jsonify({
                    "classification": "Not a CBC result",
                    "confidence_score": "0%",
                    "explanation": "The image does not contain a readable complete blood count (CBC) result or insufficient data could be extracted.",
                    "healthrisk": "No health risk information available.",
                    "extracted_text": cbc_data['extracted_text'][:500] + "..." if len(cbc_data['extracted_text']) > 500 else cbc_data['extracted_text']
                })
            
            # Initialize Gemini model
            model = genai.GenerativeModel('gemini-2.0-flash')
            
            # Create structured prompt with extracted CBC data
            analysis_prompt = f"""
            Analyze the following CBC (Complete Blood Count) results and classify what type of anemia it is.

            Extracted CBC Values:
            {json.dumps(cbc_data['cbc_values'], indent=2)}

            Classification Types:
            - Macrocytic anemia 
            - Microcytic anemia 
            - Normocytic anemia
            - Healthy/No anemia
            - Inconclusive (insufficient data)

            Key parameters for anemia classification:
            - Hemoglobin: Low levels indicate anemia
            - MCV (Mean Corpuscular Volume): Determines type of anemia
            - RBC Count: Red blood cell count
            - Hematocrit: Percentage of blood that is red blood cells

            Normal ranges (approximate):
            - Hemoglobin: 120-160 g/L (12-16 g/dL) for women, 140-180 g/L (14-18 g/dL) for men
            - MCV: 80-100 fL
            - RBC Count: 4.5-5.5 million cells/µL for women, 4.7-6.1 million cells/µL for men
            - Hematocrit: 36-44% for women, 41-50% for men

            Important: The differential count values (Neutrophils, Lymphocytes, etc.) are provided as proportions (0.0-1.0), not percentages.

            Analyze the available values and provide:
            1. Classification based on the CBC values
            2. Confidence score reflecting certainty of analysis
            3. Detailed explanation of the classification
            4. Health risk assessment and recommendations

            Even with limited data, try to provide the best possible classification based on available parameters.

            Format your response as a JSON object:
            {{
                "classification": "Type of anemia or health status",
                "confidence_score": "Confidence score as a percentage",
                "explanation": "Detailed explanation including which values support the classification",
                "healthrisk": "Health risk information and recommendations",
                "key_values_analyzed": "Summary of the CBC values that were used for classification"
            }}

            Return ONLY the JSON object, nothing else.
            """

            # Generate analysis using Gemini
            analysis_response = model.generate_content(analysis_prompt)
            analysis_text = analysis_response.text
            
            # Clean up the response text to extract JSON
            analysis_text = analysis_text.strip()
            if analysis_text.startswith("```json"):
                analysis_text = analysis_text[7:]
            if analysis_text.endswith("```"):
                analysis_text = analysis_text[:-3]
            
            try:
                analysis_data = json.loads(analysis_text.strip())
            except json.JSONDecodeError:
                # Fallback if JSON parsing fails
                return jsonify({
                    "classification": "Analysis Error",
                    "confidence_score": "0%",
                    "explanation": "Could not parse the AI analysis response.",
                    "healthrisk": "Please consult a healthcare professional for proper CBC interpretation.",
                    "raw_response": analysis_text,
                    "extracted_cbc_data": cbc_data['cbc_values']
                })

            # Prepare final result
            result = {
                "classification": analysis_data.get("classification", "Unknown"),
                "confidence_score": analysis_data.get("confidence_score", "0%"),
                "explanation": analysis_data.get("explanation", "No explanation provided"),
                "healthrisk": analysis_data.get("healthrisk", "Consult a healthcare professional"),
                "key_values_analyzed": analysis_data.get("key_values_analyzed", ""),
                "extracted_cbc_data": cbc_data['cbc_values']
            }

            return jsonify(result)
            
        except Exception as e:
            return jsonify({
                "error": f"Processing error: {str(e)}",
                "extracted_text_preview": cbc_data.get('extracted_text', '')[:200] if 'cbc_data' in locals() else "Could not extract text"
            }), 500
            
        finally:
            # Clean up temporary file
            if os.path.exists(temp_file_path):
                os.remove(temp_file_path)
                
    except Exception as e:
        return jsonify({"error": f"Server error: {str(e)}"}), 500

@app.route("/test_ocr", methods=["POST"])
def test_ocr():
    """Test endpoint to check OCR extraction without AI analysis"""
    try:
        image_data = request.json.get("image")
        if not image_data:
            return jsonify({"error": "No image data provided"}), 400
        
        base64_image = image_data.split(",")[1]
        binary_image = base64.b64decode(base64_image)

        with tempfile.NamedTemporaryFile(delete=False, suffix=".png") as temp_file:
            temp_file.write(binary_image)
            temp_file_path = temp_file.name

        try:
            extractor = CBCExtractor()
            cbc_data = extractor.extract_cbc_data(temp_file_path)
            
            return jsonify({
                "extracted_text": cbc_data['extracted_text'],
                "cbc_values": cbc_data['cbc_values'],
                "is_valid_cbc": cbc_data['is_valid_cbc']
            })
            
        finally:
            if os.path.exists(temp_file_path):
                os.remove(temp_file_path)
                
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    port = 8800
    print(f"Starting server on http://localhost:{port}")
    print("Available endpoints:")
    print("- POST /predict_anemia - Analyze CBC image for anemia")
    print("- POST /test_ocr - Test OCR extraction only")
    app.run(debug=True, port=port)