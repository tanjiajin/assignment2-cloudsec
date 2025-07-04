FROM php:8.1-apache

# Fix Apache hostname warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Install dependencies and tools
RUN apt-get update && apt-get install -y \
    gnupg2 \
    curl \
    apt-transport-https \
    lsb-release \
    ca-certificates \
    unixodbc \
    unixodbc-dev \
    software-properties-common \
    libgssapi-krb5-2 \
    krb5-multidev \
    libcurl4-openssl-dev \
    libssl-dev \
    libstdc++6 \
    odbcinst \
    wget \
    gpg \
    unzip \
    git \
    pkg-config

# Add Microsoft package repo for Debian 11 (bullseye)
RUN curl https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /etc/apt/trusted.gpg.d/microsoft.gpg \
    && curl -sSL https://packages.microsoft.com/config/debian/11/prod.list > /etc/apt/sources.list.d/mssql-release.list

# Update and install Microsoft ODBC Driver 18 + Tools
RUN apt-get update && ACCEPT_EULA=Y apt-get install -y \
    msodbcsql18 \
    mssql-tools18 \
    && ln -sfn /opt/mssql-tools18/bin/sqlcmd /usr/bin/sqlcmd \
    && ln -sfn /opt/mssql-tools18/bin/bcp /usr/bin/bcp

# Install PHP extensions for SQL Server
RUN pecl install pdo_sqlsrv sqlsrv \
    && docker-php-ext-enable pdo_sqlsrv sqlsrv \
    && docker-php-ext-install pdo mysqli

# Enable Apache rewrite if needed
RUN a2enmod rewrite

# Copy app files into Apache root
COPY . /var/www/html/

# Set default ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose HTTP port
EXPOSE 80

