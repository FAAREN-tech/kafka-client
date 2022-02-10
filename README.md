# Laravel Kafka Client

This package offers a simple Kafka-Client-Wrapper with Laravel Facades and configs

## Installation

Run the following commands:

```shell
// Install the package
composer require faaren-tech/kafka-client

// Publish config
php artisan vendor:publish --provider="FaarenTech\KafkaClient\KafkaClientServiceProvider" 
```

## Configuration Parameter
The parameters are prepared and needs to be added to your .env
````
FAA_KAFKA_REGISTRY_URL=XXXX
FAA_KAFKA_REGISTRY_AUTH_USER=XXXX
FAA_KAFKA_REGISTRY_AUTH_PASSWORD=XXXX

FAA_KAFKA_BROKER_URL=XXXX
FAA_KAFKA_BROKER_AUTH_USER=XXXX
FAA_KAFKA_BROKER_AUTH_PASSWORD=XXXX
FAA_KAFKA_CONSUMER_GROUP_ID=XXXX
````

### Producer
```php
            Producer::topic('TopicName')
                ->buildProducerWithRegistry('BodySchema', 'KeySchema')
                ->message('KeyValue', 'Data2Send', 'headers', 'partition')
                ->produce();
            Producer::flush();
```
### Consumer
```php

        $consumer = Consumer::topic('TopicName')
            ->buildConsumerWithRegistry('BodySchema', 'KeySchema')
            ->subscribe();

        while(true) {       
            /**
              * First Parameter, is the Timeout in ms, Second Parameter is the Auto-Decode Option 
            */
            $consumer->consume(5000, false, function($consumer, $message){
                //to "commit" the message, please use the ack method
                $consumer->ack($message);
            });
            
        }
```