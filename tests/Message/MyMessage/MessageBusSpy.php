<?php

declare(strict_types=1);

namespace App\Tests\Message\MyMessage;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusSpy implements MessageBusInterface
{
    public array $info = [];

    public function dispatch(Object $message, array $stamps = []): Envelope
    {
        $this->info[] = $message;
        return new Envelope(new \stdClass());
    }
}
