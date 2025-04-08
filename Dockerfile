# Use official Python image
FROM python:3.10-slim

# Set working directory
WORKDIR /app

# Copy requirements and install dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy rest of the project files
COPY . .

# Expose the app port (change if needed)
EXPOSE 8000

# Command to run the app (adjust as needed)
CMD ["python", "app.py"]
