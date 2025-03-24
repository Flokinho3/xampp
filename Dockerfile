# Usa uma imagem do PHP com Apache
FROM php:7.4-apache

# Instala dependências do sistema e o MySQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    wget \
    git \
    mariadb-client \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd mysqli pdo pdo_mysql zip

# Copia os arquivos do seu projeto para o servidor web
COPY . /var/www/html/

# Define permissões
RUN chown -R www-data:www-data /var/www/html/

# Expõe a porta 80 para acesso ao Apache
EXPOSE 80

# Comando para iniciar o Apache
CMD ["apache2-foreground"]
