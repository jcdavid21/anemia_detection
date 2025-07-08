from flask import Flask, request, jsonify
import os
import base64
from dotenv import load_dotenv
import tempfile
from google import generativeai as genai
from flask_cors import CORS
import json

load_dotenv()

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

API_KEY = os.getenv("GOOGLE_API_KEY", "AIzaSyCzVwiwU-DBYVlgcXOgg9-4NUpd9Hjw5iA")
genai.configure(api_key=API_KEY)

@app.route("/predict_anemia", methods=["POST"])
def predict_anemia():
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
            model = genai.GenerativeModel('gemini-2.0-flash')

            # Upload image file
            with open(temp_file_path, 'rb') as f:
                uploaded_file = genai.upload_file(f, mime_type="image/png")

                analysis_prompt = """
                Analyze the uploaded image and classify what type of anemia it is.
                Types of anemia:
                - Macrocytic anemia
                - Microcytic anemia
                - Normocytic anemia
                - Health no anemia
                - Not a CBC result

                Provide a detailed explanation of the classification and any relevant information.
            
                Provide healthrisk information and recommendations for further action or treatment.

                if the image is not a CBC result return or your cannot identify the type of anemia return this JSON object:
                
                {
                    "classification": "Not a CBC result",
                    "confidence_score": "0%",
                    "explanation": "The image does not contain a complete blood count (CBC) result.",
                    "healthrisk": "No health risk information available.",
                }

                For each identification, provide a confidence score that accurately reflects your certainty:
                    - 90-100%: Very high confidence with clear visual evidence
                    - 70-89%: Good confidence with some distinctive features visible
                    - 50-69%: Moderate confidence with partial or unclear features
                    - 30-49%: Low confidence, educated guess based on limited visual cues
                    - Below 30%: Very uncertain, minimal distinguishing features visible
                    
                    Format your response as a JSON object with the following structure:
                    {
                        "classification": "Type of anemia",
                        "confidence_score": "Confidence score as a percentage",
                        "explanation": "Detailed explanation of the classification",
                        "healthrisk": "Health risk information",
                    }

                Return ONLY the JSON object, nothing else.
                """

                analysis_response = model.generate_content([uploaded_file, analysis_prompt])
                analysis_text = analysis_response.text
            
                # Clean up the response text to extract only the JSON
                analysis_text = analysis_text.strip()
                if analysis_text.startswith("```json"):
                    analysis_text = analysis_text[7:]
                if analysis_text.endswith("```"):
                    analysis_text = analysis_text[:-3]
                
                analysis_data = json.loads(analysis_text.strip())

                result = {
                    "classification": analysis_data["classification"],
                    "confidence_score": analysis_data["confidence_score"],
                    "explanation": analysis_data["explanation"],
                    "healthrisk": analysis_data["healthrisk"],
                }

                return jsonify(result)
        finally:
            #clean up the temporary file
            if os.path.exists(temp_file_path):
                os.remove(temp_file_path)
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    

if __name__ == '__main__':
    port = 8800
    print(f"Starting server on http://localhost:{port}")
    app.run(debug=True, port=port)



