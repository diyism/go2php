//how to compile php7.3(disable redeclare fatal error)+swoole4.3.0+runkit7:

$sudo apt-get install re2c bison autoconf zlib1g-dev libssl-dev libcurl4-openssl-dev libargon2-0-dev libsodium-dev

//"apt-get install libpq-dev" needed by "--with-pdo-pgsql=/usr/bin/pg_config" when "./configure"

$git clone -b master --depth 1 https://github.com/diyism/go2php.git

#disabled "redeclare fatal error" of php function and class: https://github.com/diyism/go2php/commit/661eff9094be70e9c38899c5505c54471511fed7

$cd go2php-src/

$rm configure

$./buildconf --force

$./configure --prefix=/usr/local/php7 --disable-all --enable-cli --disable-cgi --disable-fpm --disable-phpdbg --enable-bcmath \

--enable-hash --enable-json --enable-mbstring --enable-mbregex --enable-mbregex-backtrack --enable-sockets --enable-pdo \

--with-sodium --with-password-argon2 --with-sqlite3 --with-pdo-sqlite --with-pdo-mysql --with-pdo-pgsql=/usr/bin/pg_config --with-pcre-regex \

--with-zlib --with-openssl-dir --enable-openssl --enable-session --enable-swoole --enable-runkit

$time make -j `cat /proc/cpuinfo | grep processor | wc -l`

$sudo make install

$sudo rm -f /usr/bin/php

$sudo ln -s /usr/local/php7/bin/php /usr/bin/php

//start go2php http server:

$php go2php_server.php

//test from web browser:

http://127.0.0.1/test.php

//current flaws:

//1. we need to batch replace "$_GET/$_POST/$_COOKIE/$_SESSION" into "_GET()/_POST()/_COOKIE()/_SESSION()" in target normal php projects.

//2. when modified php files, the functions and classes in it won't be reloaded
