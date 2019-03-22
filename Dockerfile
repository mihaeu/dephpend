FROM php:7.3-cli
COPY . /dephpend
WORKDIR /dephpend
ENTRYPOINT [ "php", "./bin/dephpend" ]