# Use official PHP image
FROM php:8.2-cli

# Set working directory
WORKDIR /app

# Copy project files
COPY . /app

# Expose port
EXPOSE 10000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:10000", "bot.php"]
