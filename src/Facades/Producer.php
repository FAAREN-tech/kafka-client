<?php

namespace FaarenTech\KafkaClient\Facades;
use Illuminate\Support\Facades\Facade;

class Producer extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'producer';
    }

}