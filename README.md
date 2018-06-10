# cache-component

[![Build Status](https://travis-ci.org/chilimatic/cache-component.svg?branch=master)](https://travis-ci.org/chilimatic/cache-component)

Currently there are 3 adapters: 

Memcached, Apcu and Memory

It also comes with a singelton wrapper but you don't have to use it. 

```php
$cache = Cache::getInstance(['type' => chilimatic\lib\Cache\Engine\Adapter\Memory::class]);
```

If you want to use a Factory

```php
$cache = CacheFactory::make(chilimatic\lib\Cache\Engine\Adapter\Memory::class);
```

And ofc you can access them directly 

```php
$cache = new chilimatic\lib\Cache\Engine\Adapter\Memory();
```

you can pass in arrays or stdClass it will transform it to an array anyway (nested too)
```php
$cache = new chilimatic\lib\Cache\Engine\Adapter\Memcached([
    'server_list' => [[
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 1
    ]],
    'options' => [ // the options are per instance not per connection!
        Memcached::OPT_HASH => Memcached::HASH_MURMUR
    ]
]);
```
more about the possible options http://php.net/manual/en/memcached.constants.php

```php
$cache = new chilimatic\lib\Cache\Engine\Adapter\APCU();
```

one of the benefits of this library is that it actually allows you to see what theoretically 
stored within the caches

```php 
$cache = chilimatic\lib\Cache\Engine\Adapter\Memory();

var_dump($cache->listEntries());
```

it has 1 dependency the chilimatic interfaces. 

if you want to know how things work I recommend looking at the tests :)