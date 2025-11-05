<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\BlogController;
use App\Entity\Feedback;
use App\Repository\FeedbackRepository;
use App\Service\FeedbackProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class BlogControllerTest extends TestCase
{
    public function testShowFeedbackRendersEmptyWhenNoFeedback(): void
    {
        $repo = $this->createMock(FeedbackRepository::class);
        $provider = $this->createMock(FeedbackProvider::class);

        $repo->method('findAll')->willReturn([]);
        $provider->expects($this->never())->method('transformDataForTwig');

        $controller = new BlogController($repo, $provider);

        // symulacja wywołania metody z mockiem render()
        $controller = $this->getMockBuilder(BlogController::class)
            ->setConstructorArgs([$repo, $provider])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('feedback/feedback.html.twig', [])
            ->willReturn(new Response('OK'));

        $response = $controller->showFeedback();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    public function testShowFeedbackRendersTransformedFeedback(): void
    {
        $repo = $this->createMock(FeedbackRepository::class);
        $provider = $this->createMock(FeedbackProvider::class);

        $feedback = [new Feedback()];
        $repo->method('findAll')->willReturn($feedback);

        $expectedData = ['feedbackList' => ['demo']];
        $provider->expects($this->once())
            ->method('transformDataForTwig')
            ->with($feedback)
            ->willReturn($expectedData);

        $controller = $this->getMockBuilder(BlogController::class)
            ->setConstructorArgs([$repo, $provider])
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with('feedback/feedback.html.twig', $expectedData)
            ->willReturn(new Response('Rendered'));

        $response = $controller->showFeedback();
        $this->assertSame('Rendered', $response->getContent());
    }
}
