<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\PostController;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Minimal stub to isolate PostController logic.
 */
class PostControllerStub extends PostController
{
    private ?UserInterface $mockUser = null;

    public function setMockUser(?UserInterface $user): void
    {
        $this->mockUser = $user;
    }

    public function getUser(): ?UserInterface
    {
        return $this->mockUser;
    }

    // Override json() to avoid Symfony container usage
    protected function json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
}

class PostControllerTest extends TestCase
{
    public function testListReturnsAllPosts(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(1);
        $post->method('getTitle')->willReturn('Test Title');
        $post->method('getDescription')->willReturn('Desc');
        $post->method('getTags')->willReturn('tag1,tag2');
        $post->method('getType')->willReturn('bug');
        $post->method('getAuthor')->willReturn(null);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findAll')->willReturn([$post]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $controller = new PostControllerStub();
        $response = $controller->list($em);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertSame('Test Title', $data[0]['title']);
    }

    public function testShowThrowsIfPostNotFound(): void
    {
        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $controller = new PostControllerStub();
        $this->expectException(NotFoundHttpException::class);
        $controller->show(999, $em);
    }

    public function testShowReturnsPostData(): void
    {
        $post = $this->createMock(Post::class);
        $post->method('getId')->willReturn(7);
        $post->method('getTitle')->willReturn('Sample');
        $post->method('getDescription')->willReturn('Hello');
        $post->method('getTags')->willReturn('php,unit');
        $post->method('getType')->willReturn('solution');
        $post->method('getAuthor')->willReturn(null);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('find')->willReturn($post);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repo);

        $controller = new PostControllerStub();
        $response = $controller->show(7, $em);
        $data = json_decode($response->getContent(), true);

        $this->assertSame('Sample', $data['title']);
    }

    public function testAddReturnsUnauthorizedWithoutUser(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'title' => 'x', 'description' => 'y', 'type' => 'bug'
        ]));
        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new PostControllerStub();
        $controller->setMockUser(null);

        $response = $controller->add($request, $em);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAddReturns400WhenMissingFields(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode(['title' => 'OnlyTitle']));
        $em = $this->createMock(EntityManagerInterface::class);

        $controller = new PostControllerStub();
        $controller->setMockUser(new User());

        $response = $controller->add($request, $em);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testAddCreatesPostSuccessfully(): void
    {
        $data = [
            'title' => 'New Post',
            'description' => 'Post description',
            'type' => 'bug',
            'tags' => ['a', 'b']
        ];

        $request = new Request([], [], [], [], [], [], json_encode($data));

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $controller = new PostControllerStub();
        $controller->setMockUser(new User());

        $response = $controller->add($request, $em);
        $this->assertSame(201, $response->getStatusCode());
    }
}
