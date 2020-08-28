FROM lorisleiva/laravel-docker

RUN docker-php-ext-install opcache

RUN wget https://www.sqlite.org/2020/sqlite-autoconf-3330000.tar.gz \
   &&  tar xvfz sqlite-autoconf-3330000.tar.gz \
   && cd sqlite-autoconf-3330000 \
   &&  ./configure --prefix=/usr/local \
   &&  make \
   &&  make install

RUN rm -rf /usr/local/lib/libsqlite3.so.0