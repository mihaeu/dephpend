FROM php:8.2-cli
RUN mkdir -p /usr/share/man/man1 \
    && apt-get update && apt-get install -y \
        default-jdk \
        default-jdk-headless \
    && dpkg --configure -a \
    && apt-get install -y \
        graphviz \
        plantuml
COPY . /dephpend
WORKDIR /dephpend
COPY --link --from=composer:2.8.11 /usr/bin/composer /usr/local/bin/composer
RUN composer install
ENTRYPOINT [ "php", "-n", "-d memory_limit=-1", "./bin/dephpend" ]
