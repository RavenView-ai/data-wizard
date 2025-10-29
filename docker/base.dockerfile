FROM dunglas/frankenphp:1.9-php8.3-trixie as frankenphp

LABEL authors="Lukas Mateffy <hey@mateffy.me>"

RUN install-php-extensions \
	pdo_sqlite \
    pdo_pgsql \
	mbstring \
    exif \
	gd \
	intl \
	zip \
	opcache \
    ffi \
    imagick \
    sodium \
    pcntl \
    sockets \
    bcmath

# Install libmagic (for Python Mimetype detection)
RUN apt-get update -y && apt-get install -y \
    libmagic-dev \
    unzip \
    git \
    unzip \
    libpq-dev \
    supervisor \
    libreoffice \
    libreoffice-java-common \
    build-essential \
    pkg-config \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libopenjp2-7-dev \
    libtiff-dev \
    libharfbuzz-dev \
    libfribidi-dev \
    libglu1-mesa-dev \
    libxcursor-dev \
    libxrandr-dev \
    libxinerama-dev \
    libxi-dev \
    libcairo2-dev \
    libgirepository1.0-dev \
    libffi-dev \
    python3-dev \
    python3-pip \
    python3-setuptools \
    python3-wheel \
    tesseract-ocr

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install bun
RUN curl -fsSL https://bun.sh/install | bash