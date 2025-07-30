FROM laravelsail/php82-composer

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libcurl4-openssl-dev \
    libssl-dev \
    pkg-config \
    zlib1g-dev \
    libpcre3-dev \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Clone Swoole dan compile tanpa brotli
RUN git clone https://github.com/swoole/swoole-src.git /tmp/swoole \
    && cd /tmp/swoole \
    && phpize \
    && ./configure --enable-swoole --enable-openssl=no --enable-brotli=no \
    && make -j$(nproc) && make install \
    && docker-php-ext-enable swoole \
    && rm -rf /tmp/swoole

# Set working directory
WORKDIR /var/www/html

# Create entrypoint script
RUN echo '#!/bin/bash\n\
# Wait for database\n\
until nc -z mysql 3306; do\n\
  echo "Waiting for MySQL..."\n\
  sleep 2\n\
done\n\
\n\
# Wait for Redis\n\
until nc -z redis 6379; do\n\
  echo "Waiting for Redis..."\n\
  sleep 2\n\
done\n\
\n\
echo "Starting Laravel Octane with Swoole..."\n\
exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=8080 --workers=1' > /usr/local/bin/octane-start.sh

RUN chmod +x /usr/local/bin/octane-start.sh

CMD ["/usr/local/bin/octane-start.sh"]