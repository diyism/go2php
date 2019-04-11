--TEST--
swoole_redis_coro: redis unconnected recv
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';
go(function () {
    $redis = new Swoole\Coroutine\Redis;
    $redis->setDefer(true);
    assert($redis->recv() === false);
    echo "DONE\n";
});
?>
--EXPECT--
DONE
