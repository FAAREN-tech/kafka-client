<?php

namespace FaarenTech\KafkaClient\Facades;
use Illuminate\Support\Facades\Facade;

class Consumer extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'consumer';
    }

}