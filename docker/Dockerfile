FROM ubuntu:bionic-20190612
MAINTAINER fffilimonov <fffilimonov@yandex.ru>

ENV DEBIAN_FRONTEND noninteractive
ENV DEBCONF_NONINTERACTIVE_SEEN true
ENV TZ "US/Pacific"
ENV LANGUAGE en_US.UTF-8
ENV LANG en_US.UTF-8

USER root

RUN  echo "deb http://archive.ubuntu.com/ubuntu bionic main universe\n" > /etc/apt/sources.list \
  && echo "deb http://archive.ubuntu.com/ubuntu bionic-updates main universe\n" >> /etc/apt/sources.list \
  && echo "deb http://security.ubuntu.com/ubuntu bionic-security main universe\n" >> /etc/apt/sources.list

RUN apt-get update -qqy \
  && apt-get -qqy --no-install-recommends install \
    language-pack-en \
    locales \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/*

#locale
RUN locale-gen en_US.UTF-8 \
  && dpkg-reconfigure --frontend noninteractive locales \
  && apt-get update -qqy \
  && apt-get -qqy --no-install-recommends install \
    language-pack-en

RUN apt-get update -qqy \
  && apt-get -qqy --no-install-recommends install \
    tzdata \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/*

#Timezone
RUN echo "${TZ}" > /etc/timezone \
  && dpkg-reconfigure --frontend noninteractive tzdata

#Java
RUN apt-get update -qqy \
  && apt-get -qqy --no-install-recommends install \
    bzip2 \
    ca-certificates \
    openjdk-8-jre-headless \
    sudo \
    unzip \
    vim \
    wget \
  && sed -i 's/securerandom\.source=file:\/dev\/random/securerandom\.source=file:\/dev\/urandom/' ./usr/lib/jvm/java-8-openjdk-amd64/jre/lib/security/java.security

#Selenium
RUN  mkdir -p /opt/selenium \
  && wget --no-verbose https://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar -O /opt/selenium/selenium-server-standalone.jar

#Xvfb
RUN apt-get update -qqy \
  && apt-get -qqy install \
    xvfb

# Chrome webdriver
RUN wget -nv "https://chromedriver.storage.googleapis.com/76.0.3809.126/chromedriver_linux64.zip" \
          -O "chromedriver_linux.zip" \
  && unzip "chromedriver_linux.zip" \
  && rm "chromedriver_linux.zip" \
  && chmod 755 chromedriver \
  && mv chromedriver /usr/bin/

# libs for chrome driver
RUN apt-get update -qqy \
  && apt-get -qqy --no-install-recommends install \
    libgconf-2-4 \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/*

# Google Chrome
RUN apt-get -qqy update \
  && wget -nv "https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb" \
          -O "google-chrome-stable_current_amd64.deb" \
  && apt -qyy --no-install-recommends install \
        "./google-chrome-stable_current_amd64.deb" \
  && rm "google-chrome-stable_current_amd64.deb"

RUN mv /opt/google/chrome/google-chrome /opt/google/chrome/google-chrome-base
COPY google-chrome /opt/google/chrome/google-chrome
RUN chmod +x /opt/google/chrome/google-chrome

#PHP and Redis
RUN apt-get update -qqy \
  && apt-get -qqy --no-install-recommends install \
    php7.2 \
    php7.2-mbstring \
    php7.2-curl \
    php7.2-xml \
    php-redis \
    redis-tools \
    composer \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/*

RUN  mkdir -p /opt/codeception
WORKDIR '/opt/codeception'
COPY composer.json /opt/codeception
RUN composer install
RUN ./vendor/bin/codecept init acceptance
COPY codeception.yml /opt/codeception
COPY ./acceptance.suite.yml /opt/codeception/tests
COPY ./AcceptanceTester.php /opt/codeception/tests/_support
COPY ./ScreenShotModule.php /opt/codeception/tests/_support

COPY selenium /etc/init.d/selenium
RUN chmod +x /etc/init.d/selenium

COPY entry_point.sh /opt/bin/entry_point.sh
RUN chmod +x /opt/bin/entry_point.sh

CMD ["/opt/bin/entry_point.sh"]
