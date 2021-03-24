//use namespace global_functions to replace runkit7 extension, but the side effect is we should add a line 
//"use function global_functions\session_start, global_functions\_SESSION, global_functions\session_write_close, global_functions\_COOKIE, global_functions\header, global_functions\_POST, global_functions\_GET, global_functions\_SERVER;"
//on top of every php files
namespace global_functions;

/*how to compile php7.3(disable redeclare fatal error)+swoole4.6.0:
$sudo apt-get install re2c bison autoconf zlib1g-dev libssl-dev libcurl4-openssl-dev libargon2-0-dev libsodium-dev
//apt install libssl1.1 libargo2-0 libpq5 libstdc++6 libc6
//or without compile: apt install php7.4-json php7.4-cli php7.4-dev php7.4-xml php-pear php7.4-mysql php7.4-json, ln -s /usr/bin/phpize7.4 /usr/bin/phpize, ln -s /usr/bin/php-config7.4 /usr/bin/php-config, pecl install swoole, if pecl say xml error, do "apt purge php*-xml" first
//"apt-get install libpq-dev" needed by "--with-pdo-pgsql=/usr/bin/pg_config" when "./configure"
$git clone -b PHP-7.3 --depth 1 https://github.com/php/php-src.git
#to disable "redeclare fatal error" of php function and class: https://github.com/diyism/go2php/commit/970dbe6db00b5ec49786450591b3128abf646de0
$cd php-src/
$mkdir ext/swoole && curl -s "https://codeload.github.com/swoole/swoole-src/tar.gz/v4.6.0" | tar xvz --strip 1 -C ext/swoole
//$mkdir ext/runkit7 && curl -s "https://codeload.github.com/runkit7/runkit7/tar.gz/1.0.11" | tar xvz --strip 1 -C ext/runkit7
$rm configure
$./buildconf --force
$./configure --prefix=/usr/local/php7 --disable-all --enable-cli --disable-cgi --disable-fpm --disable-phpdbg --enable-bcmath \
--enable-hash --enable-json --enable-mbstring --enable-mbregex --enable-mbregex-backtrack --enable-sockets --enable-pdo \
--with-sodium --with-password-argon2 --with-sqlite3 --with-pdo-sqlite --with-pdo-mysql --with-pdo-pgsql=/usr/bin/pg_config --with-pcre-regex \
--with-zlib --with-openssl-dir --enable-openssl --enable-session --enable-swoole
// --enable-runkit
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
//2. must add a line "use function global_functions\session_start..." on top of every php files(if get rid of runkit7)
//3. when modified php files, the functions and classes in them won't be reloaded
