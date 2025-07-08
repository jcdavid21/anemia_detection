# CBC Model Testing & Prediction Script
# Test your trained CBC classification model on new images

import os
import numpy as np
import matplotlib.pyplot as plt
import cv2
from PIL import Image
import tensorflow as tf
from tensorflow import keras
from sklearn.preprocessing import LabelEncoder
import pickle
import warnings
warnings.filterwarnings('ignore')

print("TensorFlow version:", tf.__version__)

# =============================================================================
# METHOD 1: LOAD PRE-TRAINED MODEL
# =============================================================================

def load_trained_model(model_path='cbc_classification_model_final.h5'):
    """Load the trained model"""
    try:
        print(f"Loading model from: {model_path}")
        model = keras.models.load_model(model_path)
        print("✅ Model loaded successfully!")
        return model
    except Exception as e:
        print(f"❌ Error loading model: {e}")
        print("Make sure the model file exists and was saved properly.")
        return None

# =============================================================================
# METHOD 2: RECREATE LABEL ENCODER (if not saved)
# =============================================================================

def create_label_encoder():
    """Recreate the label encoder with the same classes used during training"""
    # These should match the classes from your training data
    classes = [
        'Healthy no anemia_Female', 'Healthy no anemia_Male',
        'Macrocytic_Female', 'Macrocytic_Male',
        'Microcytic_Female', 'Microcytic_Male',
        'Normocytic_Female', 'Normocytic_Male'
    ]
    
    label_encoder = LabelEncoder()
    label_encoder.fit(classes)
    print(f"Label encoder created with {len(classes)} classes:")
    for i, class_name in enumerate(classes):
        print(f"  {i}: {class_name}")
    
    return label_encoder

# =============================================================================
# METHOD 3: IMAGE PREPROCESSING
# =============================================================================

def preprocess_image(image_path, target_size=(224, 224)):
    """Preprocess a single image for prediction"""
    try:
        # Load image
        img = cv2.imread(image_path)
        if img is None:
            raise ValueError(f"Could not load image from {image_path}")
        
        # Convert BGR to RGB
        img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        
        # Resize to target size
        img = cv2.resize(img, target_size)
        
        # Normalize pixel values to [0, 1]
        img = img.astype(np.float32) / 255.0
        
        # Add batch dimension
        img = np.expand_dims(img, axis=0)
        
        return img
    
    except Exception as e:
        print(f"❌ Error preprocessing image: {e}")
        return None

# =============================================================================
# METHOD 4: MAKE PREDICTIONS
# =============================================================================

def predict_single_image(model, image_path, label_encoder, show_image=True):
    """Predict the class of a single image"""
    print(f"\n🔍 Analyzing image: {os.path.basename(image_path)}")
    print("-" * 50)
    
    # Preprocess the image
    processed_img = preprocess_image(image_path)
    if processed_img is None:
        return None
    
    # Make prediction
    try:
        predictions = model.predict(processed_img, verbose=0)
        predicted_class_idx = np.argmax(predictions[0])
        confidence = predictions[0][predicted_class_idx]
        
        # Get class name
        predicted_class = label_encoder.classes_[predicted_class_idx]
        
        print(f"📊 Prediction Results:")
        print(f"   Predicted Class: {predicted_class}")
        print(f"   Confidence: {confidence:.4f} ({confidence*100:.2f}%)")
        
        # Show top 3 predictions
        top_3_indices = np.argsort(predictions[0])[-3:][::-1]
        print(f"\n🏆 Top 3 Predictions:")
        for i, idx in enumerate(top_3_indices, 1):
            class_name = label_encoder.classes_[idx]
            conf = predictions[0][idx]
            print(f"   {i}. {class_name}: {conf:.4f} ({conf*100:.2f}%)")
        
        # Display the image
        if show_image:
            plt.figure(figsize=(8, 6))
            original_img = cv2.imread(image_path)
            original_img = cv2.cvtColor(original_img, cv2.COLOR_BGR2RGB)
            plt.imshow(original_img)
            plt.title(f'Prediction: {predicted_class}\nConfidence: {confidence:.4f}')
            plt.axis('off')
            plt.show()
        
        return {
            'predicted_class': predicted_class,
            'confidence': confidence,
            'all_predictions': predictions[0],
            'class_probabilities': dict(zip(label_encoder.classes_, predictions[0]))
        }
        
    except Exception as e:
        print(f"❌ Error making prediction: {e}")
        return None

# =============================================================================
# METHOD 5: BATCH PREDICTION
# =============================================================================

def predict_multiple_images(model, image_folder, label_encoder, max_images=10):
    """Predict classes for multiple images in a folder"""
    print(f"\n🔍 Analyzing images in folder: {image_folder}")
    print("=" * 60)
    
    if not os.path.exists(image_folder):
        print(f"❌ Folder not found: {image_folder}")
        return None
    
    # Get all image files
    image_extensions = ('.png', '.jpg', '.jpeg', '.bmp', '.tiff', '.PNG', '.JPG', '.JPEG')
    image_files = [f for f in os.listdir(image_folder) if f.lower().endswith(image_extensions)]
    
    if not image_files:
        print("❌ No image files found in the folder")
        return None
    
    print(f"Found {len(image_files)} images. Processing up to {max_images}...")
    
    results = []
    for i, img_file in enumerate(image_files[:max_images]):
        img_path = os.path.join(image_folder, img_file)
        result = predict_single_image(model, img_path, label_encoder, show_image=False)
        if result:
            result['filename'] = img_file
            results.append(result)
    
    # Summary
    print(f"\n📊 BATCH PREDICTION SUMMARY")
    print("=" * 60)
    for result in results:
        print(f"{result['filename']}: {result['predicted_class']} ({result['confidence']:.3f})")
    
    return results

# =============================================================================
# METHOD 6: INTERACTIVE TESTING
# =============================================================================

def interactive_test():
    """Interactive testing function"""
    print("\n🤖 CBC MODEL TESTING INTERFACE")
    print("=" * 60)
    
    # Load model
    model_path = input("Enter model path (or press Enter for default 'cbc_classification_model_final.h5'): ").strip()
    if not model_path:
        model_path = 'cbc_classification_model_final.h5'
    
    model = load_trained_model(model_path)
    if model is None:
        return
    
    # Create label encoder
    label_encoder = create_label_encoder()
    
    while True:
        print(f"\n{'='*60}")
        print("CHOOSE AN OPTION:")
        print("1. Test a single image")
        print("2. Test multiple images in a folder")
        print("3. Exit")
        
        choice = input("\nEnter your choice (1-3): ").strip()
        
        if choice == '1':
            img_path = input("Enter image path: ").strip()
            if os.path.exists(img_path):
                predict_single_image(model, img_path, label_encoder, show_image=True)
            else:
                print(f"❌ Image not found: {img_path}")
        
        elif choice == '2':
            folder_path = input("Enter folder path: ").strip()
            max_imgs = input("Maximum images to process (default 10): ").strip()
            max_imgs = int(max_imgs) if max_imgs.isdigit() else 10
            predict_multiple_images(model, folder_path, label_encoder, max_imgs)
        
        elif choice == '3':
            print("👋 Goodbye!")
            break
        
        else:
            print("❌ Invalid choice. Please enter 1, 2, or 3.")

# =============================================================================
# METHOD 7: QUICK TEST EXAMPLES
# =============================================================================

def quick_test_examples():
    """Quick test with some example scenarios"""
    print("\n🚀 QUICK TEST EXAMPLES")
    print("=" * 60)
    
    # Load model
    model = load_trained_model()
    if model is None:
        print("❌ Cannot run quick test - model not found")
        return
    
    # Create label encoder
    label_encoder = create_label_encoder()
    
    # Example 1: Test a single image (you need to provide the path)
    print("\n📝 Example 1: Single Image Prediction")
    test_image_path = "static/uploads/43.png"  # CHANGE THIS PATH
    
    if os.path.exists(test_image_path):
        result = predict_single_image(model, test_image_path, label_encoder)
    else:
        print(f"⚠️  Example image not found: {test_image_path}")
        print("   Please update the path to test with your own image")
    
    # Example 2: Test with validation images (if available)
    print("\n📝 Example 2: Testing with Validation Data")
    test_folder = "dataset/DATASET/Healthy no anemia/Female"  # CHANGE THIS PATH
    
    if os.path.exists(test_folder):
        results = predict_multiple_images(model, test_folder, label_encoder, max_images=3)
    else:
        print(f"⚠️  Example folder not found: {test_folder}")
        print("   Please update the path to test with your own images")

# =============================================================================
# METHOD 8: MODEL INFORMATION
# =============================================================================

def show_model_info(model_path='cbc_classification_model_final.h5'):
    """Show information about the trained model"""
    model = load_trained_model(model_path)
    if model is None:
        return
    
    print(f"\n📋 MODEL INFORMATION")
    print("=" * 60)
    
    # Model summary
    print("Model Architecture:")
    model.summary()
    
    # Model details
    print(f"\nModel Details:")
    print(f"Input Shape: {model.input_shape}")
    print(f"Output Shape: {model.output_shape}")
    print(f"Total Parameters: {model.count_params():,}")
    
    # Expected classes
    label_encoder = create_label_encoder()
    print(f"\nExpected Classes ({len(label_encoder.classes_)}):")
    for i, class_name in enumerate(label_encoder.classes_):
        print(f"  {i}: {class_name}")

# =============================================================================
# MAIN EXECUTION
# =============================================================================

if __name__ == "__main__":
    print("🩸 CBC CLASSIFICATION MODEL TESTING")
    print("=" * 60)
    
    # Check if model exists
    if os.path.exists('cbc_classification_model_final.h5'):
        print("✅ Model file found!")
        
        # Show available options
        print("\nAvailable testing methods:")
        print("1. interactive_test() - Interactive testing interface")
        print("2. quick_test_examples() - Quick test examples")
        print("3. show_model_info() - Show model information")
        print("4. predict_single_image() - Test single image")
        print("5. predict_multiple_images() - Test multiple images")
        
        print("\nExample usage:")
        print("interactive_test()  # Start interactive testing")
        print("show_model_info()   # Show model details")
        
    else:
        print("❌ Model file 'cbc_classification_model_final.h5' not found!")
        print("Please ensure you have trained the model first.")
        print("The model should be saved after successful training.")

# =============================================================================
# USAGE INSTRUCTIONS
# =============================================================================

print(f"\n{'='*60}")
print("📖 HOW TO USE THIS SCRIPT:")
print("="*60)
print("1. Make sure your model is trained and saved")
print("2. Run: interactive_test() for guided testing")
print("3. Or use individual functions:")
print("   - predict_single_image(model, 'path/to/image.jpg', label_encoder)")
print("   - predict_multiple_images(model, 'path/to/folder/', label_encoder)")
print("4. Update image paths in quick_test_examples() for testing")
print("="*60)