<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\ExceptionListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ExceptionListenerTest extends TestCase
{
    private function createEvent(\Throwable $exception): ExceptionEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    }

    private function createListener(string $env = 'prod'): ExceptionListener
    {
        $logger = $this->createMock(LoggerInterface::class);
        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('get')->with('kernel.environment')->willReturn($env);
        return new ExceptionListener($logger, $params);
    }

    public function testGenericExceptionReturns500(): void
    {
        $listener = $this->createListener();
        $event = $this->createEvent(new \RuntimeException('Server exploded'));
        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Server exploded', $data['error']['message']);
    }

    public function testNotFoundExceptionReturns404(): void
    {
        $listener = $this->createListener();
        $event = $this->createEvent(new NotFoundHttpException('Not found'));
        $listener->onKernelException($event);

        $data = json_decode($event->getResponse()->getContent(), true);
        $this->assertSame(404, $data['error']['code']);
    }

    public function testAccessDeniedReturns403(): void
    {
        $listener = $this->createListener('dev');
        $event = $this->createEvent(new AccessDeniedHttpException('Forbidden'));
        $listener->onKernelException($event);

        $data = json_decode($event->getResponse()->getContent(), true);
        $this->assertSame(403, $data['error']['code']);
        $this->assertArrayHasKey('trace', $data['error']);
    }
}
