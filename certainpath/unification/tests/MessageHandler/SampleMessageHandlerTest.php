<?php

namespace App\Tests\MessageHandler;

use App\Message\SampleMessage;
use App\Tests\FunctionalTestCase;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SampleMessageHandlerTest extends FunctionalTestCase
{
    public function testSampleMessageHandlerDispatch(): void
    {
        $bus = $this->getDefaultMessengerBus();
        $content = 'SampleMessage';
        $envelope = $bus->dispatch(new SampleMessage('SampleMessage'));

        $this->assertInstanceOf(Envelope::class, $envelope);

        $transport = $this->getMessengerTransportAsync();

        $messages = $transport->get();

        $this->assertCount(1, $messages);

        foreach ($messages as $message) {
            $this->assertSame($content, $message->getMessage()->getContent());
        }
    }

    private function getDefaultMessengerBus() : MessageBusInterface
    {
        return $this->getService(
            'messenger.default_bus'
        );
    }

    private function getMessengerTransportAsync() : DoctrineTransport
    {
        return $this->getService(
            'messenger.transport.async'
        );
    }
}