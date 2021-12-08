<?php
namespace FaarenTech\KafkaClient\Producer;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Kafka\Message\Encoder\AvroEncoder;
use Jobcloud\Kafka\Message\KafkaAvroSchema;
use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use Jobcloud\Kafka\Producer\KafkaProducerInterface;

class Producer
{
    /**
     * @var array
     */
    private array $config;

    /**
     * @var string
     */
    private string $topic;
    /**
     * @var KafkaProducerInterface
     */
    private ?KafkaProducerInterface $producer;

    /**
     * @var
     */
    private $message;
    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->config = $config;
    }
    public function topic(string $topic) {
        $this->topic = $topic;
        return $this;
    }
    public function buildProducerWithRegistry(string $bodySchema, string $keySchema = 'Key'): Producer {

        $cachedRegistry = new CachedRegistry(
            new BlockingRegistry(
                new PromisingRegistry(
                    new Client([
                        "base_uri" => $this->config['registry']['url'],
                        'auth' => [
                            $this->config['registry']['auth']['user'],
                            $this->config['registry']['auth']['password']
                        ]
                    ])
                )
            ),
            new AvroObjectCacheAdapter()
        );

        $registry = new AvroSchemaRegistry($cachedRegistry);
        $recordSerializer = new RecordSerializer($cachedRegistry);

        $registry->addBodySchemaMappingForTopic(
            $this->topic,
            new KafkaAvroSchema($bodySchema)
        );

        $registry->addKeySchemaMappingForTopic(
            $this->topic,
            new KafkaAvroSchema($keySchema)
        );

        $encoder = new AvroEncoder(
            $registry,
            $recordSerializer
        );

        $this->producer = KafkaProducerBuilder::create()
            ->withAdditionalBroker($this->config['broker']['url'])
            ->withAdditionalConfig($this->config['broker']['config'])
            ->withEncoder($encoder)
            ->build();

        return $this;
    }

    public function message(string $key, array $body, array $headers, int $partition = 0): Producer {
        $this->message = KafkaProducerMessage::create($this->topic, 0)
            ->withKey($key)
            ->withBody($body)
            ->withHeaders($headers);

        return $this;
    }

    public function produce() {
        $this->producer->produce($this->message);
        $this->message = null;
    }

    public function flush(int $timeout = 5000) {
        $this->producer->flush($timeout);
    }
}