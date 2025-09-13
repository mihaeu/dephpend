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
RUN curl https://raw.githubusercontent.com/composer/getcomposer.org/a5abae68b349213793dca4a1afcaada0ad11143b/web/installer -O - -q | php -- --quiet \
    && php -n composer.phar install
ENTRYPOINT [ "php", "-n", "-d memory_limit=-1", "./bin/dephpend" ]
