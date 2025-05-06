<?php

    use Illuminate\Support\Facades\Redis;

try{
    $redis = Redis::connect('redis', 6379);
    echo 'redis working';
} catch(\Predis\Connection\ConnectionException $e){
   echo 'error connection redis';
}
