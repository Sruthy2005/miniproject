# Use an official Python runtime as a parent image
FROM python:3.10-slim

# Set environment variables
ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

# Set the working directory
WORKDIR /app

# Install dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy the rest of the code
COPY . .

# Expose port 8000 to Render
EXPOSE 8000

# Run the app with Gunicorn
# Replace app:app if your main file is not app.py
CMD ["gunicorn", "-b", "0.0.0.0:8000", "app:app"]
