FROM ubuntu:16.04
MAINTAINER fffilimonov <fffilimonov@yandex.ru>

ARG FIREFOX_VERSION=47.0.2

ENV DEBIAN_FRONTEND noninteractive
ENV DEBCONF_NONINTERACTIVE_SEEN true
ENV TZ "US/Pacific"
ENV LANGUAGE en_US.UTF-8
ENV LANG en_US.UTF-8

USER root

RUN  echo "deb http://archive.ubuntu.com/ubuntu xenial main universe\n" > /etc/apt/sources.list \
  && echo "deb http://archive.ubuntu.com/ubuntu xenial-updates main universe\n" >> /etc/apt/sources.list \
  && echo "deb http://security.ubuntu.com/ubuntu xenial-security main universe\n" >> /etc/apt/sources.list

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

#firefox
RUN apt-get update -qqy \
  && apt-get -qqy --no-install-recommends install firefox \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/* \
  && wget --no-verbose -O /tmp/firefox.tar.bz2 https://download-installer.cdn.mozilla.net/pub/firefox/releases/$FIREFOX_VERSION/linux-x86_64/en-US/firefox-$FIREFOX_VERSION.tar.bz2 \
  && apt-get -y purge firefox \
  && rm -rf /opt/firefox \
  && tar -C /opt -xjf /tmp/firefox.tar.bz2 \
  && rm /tmp/firefox.tar.bz2 \
  && mv /opt/firefox /opt/firefox-$FIREFOX_VERSION \
  && ln -fs /opt/firefox-$FIREFOX_VERSION/firefox /usr/bin/firefox

# Chrome webdriver
RUN wget -nv "https://chromedriver.storage.googleapis.com/2.27/chromedriver_linux64.zip" \
          -O "chromedriver_linux.zip" \
  && unzip "chromedriver_linux.zip" \
  && rm "chromedriver_linux.zip" \
  && chmod 755 chromedriver \
  && mv chromedriver /usr/bin/

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
    php7.0 \
    php7.0-mbstring \
    php7.0-curl \
    php-redis \
    redis-tools \
    composer \
  && rm -rf /var/lib/apt/lists/* /var/cache/apt/*

RUN  mkdir -p /opt/behat
WORKDIR '/opt/behat'
COPY composer.json /opt/behat
COPY behat.yml /opt/behat
RUN composer install
RUN  mkdir -p /opt/behat/features/bootstrap
RUN  mkdir -p /opt/behat/results
COPY index.feature /opt/behat/features
COPY FeatureContext.php /opt/behat/features/bootstrap

COPY selenium /etc/init.d/selenium
RUN chmod +x /etc/init.d/selenium

COPY entry_point.sh /opt/bin/entry_point.sh
RUN chmod +x /opt/bin/entry_point.sh

CMD ["/opt/bin/entry_point.sh"]
