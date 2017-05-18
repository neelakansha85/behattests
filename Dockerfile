FROM matriphe/alpine-php:cli
MAINTAINER Neel Shah

# Install Package Dependencies
RUN apk --update add --no-cache \
	curl curl-dev git openjdk8-jre

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mkdir -p /root/tests
WORKDIR /root/tests

# Copy tests inside docker container
ADD tests /root/tests/
COPY composer.json /root/tests/
RUN cd /root/tests && composer install
ENV PATH $PATH:/root/tests/bin

ADD start.sh /root/tests/

ENTRYPOINT ["sh", "/root/tests/start.sh"]