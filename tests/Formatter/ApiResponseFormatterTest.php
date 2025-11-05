<?php

declare(strict_types=1);

namespace App\Tests\Formatter;

use App\Formatter\ApiResponseFormatter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiResponseFormatterTest extends TestCase
{
    public function testCanBuildBasicResponse(): void
    {
        $formatter = (new ApiResponseFormatter())
            ->setData(['id' => 1])
            ->setMessage('User created')
            ->setStatusCode(Response::HTTP_CREATED);

        $response = $formatter->getResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);

        $this->assertSame(['id' => 1], $data['data']);
        $this->assertSame('User created', $data['message']);
        $this->assertSame(Response::HTTP_CREATED, $data['statusCode']);
        $this->assertSame([], $data['errors']);
    }

    public function testCanIncludeErrorsAndAdditionalData(): void
    {
        $formatter = (new ApiResponseFormatter())
            ->setErrors(['invalid_email'])
            ->setAdditionalData(['meta' => 'debug'])
            ->setStatusCode(Response::HTTP_BAD_REQUEST);

        $response = $formatter->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(['invalid_email'], $data['errors']);
        $this->assertSame(['meta' => 'debug'], $data['additionalData']);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $data['statusCode']);
    }

    public function testDefaultValuesAreSet(): void
    {
        $formatter = new ApiResponseFormatter();
        $response = $formatter->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertSame('OK', $data['message']);
        $this->assertSame(Response::HTTP_OK, $data['statusCode']);
    }
}
