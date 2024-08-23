FROM php:8.2-apache

ARG DEBIAN_FRONTEND=noninteractive
# Container user and group
ARG USERNAME=commuse-back
ARG USER_UID=1000
ARG USER_GID=$USER_UID
ENV APACHE_DOCUMENT_ROOT=/app/public

RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -ri -e "s!/var/www!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN sed -ri -e "s/www-data/$USERNAME/g" /etc/apache2/envvars
RUN cd /etc/apache2/mods-enabled && ln -s ../mods-available/headers.load
RUN cd /etc/apache2/mods-enabled && ln -s ../mods-available/rewrite.load

WORKDIR /root

RUN apt-get update \
    && apt-get -y install tzdata git build-essential patch sudo vim nano tmux curl wget unzip default-jre gnupg

RUN mkdir -p /etc/apt/keyrings && \
    wget -q https://packages.mozilla.org/apt/repo-signing-key.gpg -O- | tee /etc/apt/keyrings/packages.mozilla.org.asc > /dev/null && \
    gpg --import --import-options import-show /etc/apt/keyrings/packages.mozilla.org.asc | awk '/pub/{getline; gsub(/^ +| +$/,""); if($0 == "35BAA0B33E9EB396F59CA838C0BA5CE6DC6315A3") print "\nThe key fingerprint matches ("$0").\n"; else print "\nVerification failed: the fingerprint ("$0") does not match the expected one.\n"}' && \
    echo "deb [signed-by=/etc/apt/keyrings/packages.mozilla.org.asc] https://packages.mozilla.org/apt mozilla main" | tee -a /etc/apt/sources.list.d/mozilla.list > /dev/null && \
    echo 'Package: *\nPin: origin packages.mozilla.org\nPin-Priority: 1000' | tee /etc/apt/preferences.d/mozilla && \
    apt-get update && \
    apt-get install -y firefox

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions \
    && chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions gd mysqli pdo_mysql intl zip pgsql

RUN mkdir /usr/local/nvm
ENV NVM_DIR /usr/local/nvm
ENV NODE_VERSION 18.16.0
RUN curl https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default

ENV NODE_PATH $NVM_DIR/v$NODE_VERSION/lib/node_modules
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH

RUN npm install --global yarn

# Install composer
RUN wget https://getcomposer.org/download/latest-stable/composer.phar \
    && mv composer.phar /usr/bin/composer \
    && chmod +x /usr/bin/composer

# Create a user
RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

USER $USERNAME

# To be able to create a .bash_history
WORKDIR /home/$USERNAME/hist
RUN sudo chown -R $USERNAME:$USERNAME /home/$USERNAME/hist

# Code mounted as a volume
WORKDIR /app

RUN npm install --global selenium-standalone

# Just to keep the containder running
CMD apache2-foreground
