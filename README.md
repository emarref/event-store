Consume the HTTP API exposed by an [Event Store](https://eventstore.org) server. 

### Install

```bash
composer require emarref/event-store
```

### Setup

```php
// Create a Guzzle client
// N.B it is strongly recommended to implement a caching layer in your Guzzle client to take advantage of the
// cache headers returned by the server, and significantly reduce the load on your event store. 
$http = $http = new \GuzzleHttp\Client([
    'base_uri' => 'http://localhost:2113/',
    'timeout'  => 2,
]);

// Create an Event Store Client
$client = new \Emarref\EventStore\Client($http);

// Initialise the Event Store
$eventStore = \Emarref\EventStore\EventStore::fromClient($client);
```

### Usage

```php
$stream = $eventStore->getStream('some-stream'); // An instance of Emarref\EventStore\Endpoint\Stream

$stream->writeEntry('some-event', [
    'foo' => 'bar',
]);

foreach ($stream->readBackwards() as $event) {
    // @var Emarref\EventStore\Entity\Event $event
    printf(
        "Event %s was raised at %s containing data %s\n",
        $event->getContent()->getEventId(),
        $event->getUpdated()->format(\DateTime::ATOM),
        json_encode($event->getContent()->getData())
    );
}

// Event 5b41a272-d94f-43c1-af8f-5a096dc2be21 was raised at 2018-09-28T07:22:48+00:00 containing data {"foo":"bar"}
```
