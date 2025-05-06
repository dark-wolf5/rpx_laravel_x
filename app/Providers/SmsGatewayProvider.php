<?php

$this->app->singleton('att', fn () => new ServiceA());
$this->app->singleton('tmobile', fn () => new ServiceB());
$this->app->singleton('verizon', fn () => new ServiceB());
$this->app->singleton('mint', fn () => new ServiceB());
