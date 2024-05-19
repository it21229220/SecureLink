import sys
import joblib
import json

# Load the model and vectorizer
model = joblib.load('phishing_model.pkl')
vectorizer = joblib.load('vectorizer.pkl')

# Get the URL from command line argument
url = sys.argv[1]

# Vectorize the URL
url_vec = vectorizer.transform([url])

# Predict
prediction = model.predict(url_vec)[0]

# Return the result
print(json.dumps({'is_phishing': bool(prediction)}))
