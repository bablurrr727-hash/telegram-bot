FROM php:8.2-cli
WORKDIR /app
COPY . /app
CMD ["php", "bot.php"]
