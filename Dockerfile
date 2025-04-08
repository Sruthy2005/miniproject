# Use an official PHP-Apache base image
FROM php:8.1-apache

# Enable Apache mod_rewrite (commonly used in PHP frameworks like Laravel)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all files into the container
COPY . .

# Expose port 80 for web traffic
EXPOSE 80

# Start Apache in the foreground (required for Docker containers)
CMD ["apache2-foreground"]
