<?php
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
*/
//how to start: php simplest_php_webserver.swoole.php
//Swoole\Runtime::enableCoroutine();

ini_set('display_errors', 'On');
/*ini_set('runkit.internal_override', 'on'); 
runkit_function_redefine('header', '$h', 'Co::getContext()["headers"][]=$h;');
runkit_function_add('_SERVER', '', '$tmp=Co::getContext()["request"]->server; return $tmp?$tmp:array();');
runkit_function_add('_GET', '', '$tmp=Co::getContext()["request"]->get; return $tmp?$tmp:array();');
runkit_function_add('_POST', '', '$tmp=Co::getContext()["request"]->post; return $tmp?$tmp:array();');
runkit_function_add('_COOKIE', '', '$tmp=Co::getContext()["request"]->cookie; return $tmp?$tmp:array();');
runkit_function_add('_SESSION', '', 'return Co::getContext()["request"]->session;', true);
runkit_function_redefine('session_write_close', '', '$tmp=_COOKIE()["PHPSESSID"]; if ($tmp) {file_put_contents("/tmp/".$tmp, json_encode(Co::getContext()["request"]->session));}');
runkit_function_redefine('session_start', '', '$tmp=_COOKIE()["PHPSESSID"]; if ($tmp) {Co::getContext()["request"]->session=json_decode(@file_get_contents("/tmp/".$tmp), 1);} else {$sid=uniqid();header("Set-Cookie: PHPSESSID=".$sid);Co::getContext()["request"]->cookie["PHPSESSID"]=$sid;}');
*/

function header($h) {\Co::getContext()['response']["headers"][]=$h;}
function _SERVER() {$tmp=\Co::getContext()["request"]->server; $tmp['SERVER_NAME']=\Co::getContext()['request']->header['host']; return $tmp?$tmp:array();}
function _GET() {$tmp=\Co::getContext()["request"]->get; return $tmp?$tmp:array();}
function _POST() {$tmp=\Co::getContext()["request"]->post; return $tmp?$tmp:array();}
function _COOKIE() {$tmp=\Co::getContext()["request"]->cookie; return $tmp?$tmp:array();}
function &_SESSION() {return \Co::getContext()["request"]->session; }
function session_write_close() {$tmp=_COOKIE()["PHPSESSID"]; if ($tmp) {file_put_contents("/tmp/".$tmp, json_encode(\Co::getContext()["request"]->session));}}
function session_start() {$tmp=_COOKIE()["PHPSESSID"]; if ($tmp) {\Co::getContext()["request"]->session=json_decode(@file_get_contents("/tmp/".$tmp), 1);} else {$sid=uniqid();header("Set-Cookie: PHPSESSID=".$sid);\Co::getContext()["request"]->cookie["PHPSESSID"]=$sid;}}


//$http = new swoole_http_server("127.0.0.1", 8080);
$http = new \swoole_http_server("0.0.0.0", 443, SWOOLE_PROCESS, SWOOLE_SOCK_TCP | SWOOLE_SSL);
$SITES=['domain1.com'=>['ssl_cert_file'=>'domain1.com/ssl/domain.cert.pem', 'ssl_key_file'=>'domain1.com/ssl/private.key.pem', 'docroot'=>__DIR__.'/domain1.com/www/'],
        'domain2.com'=>['ssl_cert_file'=>'domain2.com/ssl/domain.cert.pem', 'ssl_key_file'=>'domain2.com/ssl/private.key.pem', 'docroot'=>__DIR__.'/domain2.com/www/'],
        'www.domain2.com'=>['ssl_cert_file'=>'domain2.com/ssl/domain.cert.pem', 'ssl_key_file'=>'domain2.com/ssl/private.key.pem', 'docroot'=>__DIR__.'/domain2.com/www/']
       ];
$http->set(['worker_num'=>1,
            'ssl_sni_certs'=>$SITES,
            'ssl_cert_file'=>'domain1.com/ssl/domain.cert.pem', 'ssl_key_file'=>'domain1.com/ssl/private.key.pem'
          ]);
$http->on('request', function ($request, $response) use($SITES)
                     {
                         $request->request_id=\Co::getuid();
                         $request->session=[];
                         \Co::getContext()['request']=$request;

                         $docroot=@$SITES[$request->header['host']]['docroot'];
                         if (!$docroot)
                         {
                             $response->end('This domain have not been deployed!');
                         }

                         ob_start();
                         //register_shutdown_function(function($response){$html=ob_get_contents();ob_end_clean();$response->end($html);}, $response);
                         $request->server['request_uri']=$request->server['request_uri']==='/'?'/index.php':$request->server['request_uri'];
                         if (substr($request->server['request_uri'], -4)==='.php')
                         {
                             session_start();
                             try
                             {
                                 \Co::getContext()['response']["headers"]=[];
                                 chdir($docroot.dirname($request->server['request_uri']));
                                 @include $docroot.$request->server['request_uri'];
                             }
                             catch(\Swoole\ExitException $e)
                             {
                                $msg=$e->getStatus();
                                if (is_string($msg))
                                {
                                    echo $msg;
                                }
                             }
                             $html=ob_get_contents();ob_end_clean();
                             foreach (\Co::getContext()['response']["headers"] as $h)
                             {
                                 $h=explode(': ', $h);
                                 if ($h[0]==='Location')
                                 {
                                     $response->status(302);
                                 }
                                 $response->header($h[0], @$h[1]);
                             }
                             session_write_close();
                         }
                         else
                         {
                             @readfile($docroot.$request->server['request_uri']);
                             $html=ob_get_contents();ob_end_clean();
                         }

                         $response->end($html);
                     }
         );
$http->start();
