<?php
namespace FaarenTech\KafkaClient\Consumer;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Kafka\Consumer\KafkaConsumerBuilder;
use Jobcloud\Kafka\Consumer\KafkaConsumerInterface;
use Jobcloud\Kafka\Exception\KafkaConsumerConsumeException;
use Jobcloud\Kafka\Exception\KafkaConsumerEndOfPartitionException;
use Jobcloud\Kafka\Exception\KafkaConsumerTimeoutException;
use Jobcloud\Kafka\Message\Decoder\AvroDecoder;
use Jobcloud\Kafka\Message\Decoder\DecoderInterface;
use Jobcloud\Kafka\Message\KafkaAvroSchema;
use Jobcloud\Kafka\Message\KafkaConsumerMessageInterface;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;

class Consumer
{
    /**
     * @var array
     */
    private array $config;

    /**
     * @var DecoderInterface|null
     */
    private ?DecoderInterface $decoder;
    /**
     * @var string
     */
    private string $topic;
    /**
     * @var KafkaConsumerInterface
     */
    private ?KafkaConsumerInterface $consumer;

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
    public function buildConsumerWithRegistry(string $bodySchema, string $keySchema = 'Key'): Consumer {

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

        $this->decoder = new AvroDecoder(
            $registry,
            $recordSerializer
        );
        if (function_exists('pcntl_sigprocmask')) {
            pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
            $this->config['broker']['config']['internal.termination.signal'] = SIGIO;
        } else {
            $this->config['broker']['config']['queue.buffering.max.ms'] = 1;
        }

        $this->consumer = KafkaConsumerBuilder::create()
            ->withAdditionalBroker($this->config['broker']['url'])
            ->withAdditionalConfig($this->config['broker']['config'])
            ->withDecoder($this->decoder)
            ->withConsumerGroup($this->config['consumer']['group-id'])
            ->withAdditionalSubscription($this->topic)
            ->build();

        return $this;
    }

    public function subscribe() {
        $this->consumer->subscribe();
        return $this;
    }

    public function consume(int $timeout = 5000, bool $autoDecode = false, Callable $callable = null) {
        try {
            $message = $this->consumer->consume($timeout, $autoDecode);
        } catch (KafkaConsumerTimeoutException $e) {
            return;
        } catch (KafkaConsumerEndOfPartitionException $e) {
            return;
        } catch (KafkaConsumerConsumeException $e) {
            return;
        }
        if(is_null($callable)) {
            throw new \Exception('Closure needs to be defined!');
        }
        $callable($this, $message);
    }

    public function ack(KafkaConsumerMessageInterface $message) {
        $this->consumer->commit($message);
    }
}