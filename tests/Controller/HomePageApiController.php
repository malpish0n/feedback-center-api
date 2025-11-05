<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\HomePageApiController;
use App\Repository\HomePageContentRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class HomePageApiControllerTest extends TestCase
{
    public function testIndexReturnsAllItemsAsJson(): void
    {
        $repository = $this->createMock(HomePageContentRepository::class);
        $repository->expects($this->once())
            ->method('findAll')
            ->willReturn([
                ['id' => 1, 'title' => 'Welcome', 'content' => 'Hello world!'],
                ['id' => 2, 'title' => 'Update', 'content' => 'New feature released.'],
            ]);

        $controller = new HomePageApiController();

        $response = $controller->index($repository);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertSame('Welcome', $data[0]['title']);
    }
}
