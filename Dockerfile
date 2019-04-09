FROM php:7.3-cli
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
ENTRYPOINT [ "php", "./bin/dephpend" ]
