# Use official Python image
FROM python:3.10-slim

# Set working directory inside container
WORKDIR /app

# Copy requirements file and install dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy the rest of the app
COPY . .

# Expose port (commonly 8000 for Flask/FastAPI)
EXPOSE 8000

# Start the app (change app.py to your file if needed)
CMD ["python", "app.py"]
