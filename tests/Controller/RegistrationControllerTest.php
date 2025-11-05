<?php
declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\RegistrationController;
use App\Entity\User;
use App\Form\RegistrationForm;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationControllerTest extends TestCase
{
    public function testRendersRegistrationForm(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('createView')->willReturn(new FormView());
        $form->method('isSubmitted')->willReturn(false);

        $controller = new class($form) extends RegistrationController {
            private FormInterface $form;
            public function __construct(FormInterface $form) { $this->form = $form; }
            protected function createForm($type, $data = null, array $options = []): FormInterface { return $this->form; }
            protected function render(string $view, array $parameters = [], ?Response $response = null): Response {
                return new Response('Rendered '.$view);
            }
        };

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $request = new Request();

        $response = $controller->register($request, $passwordHasher, $em);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Rendered', $response->getContent());
    }

    public function testRegistersUserSuccessfully(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);

        $passwordField = $this->createMock(FormInterface::class);
        $passwordField->method('getData')->willReturn('secret123');
        $form->method('get')->with('plainPassword')->willReturn($passwordField);

        $controller = new class($form) extends RegistrationController {
            private FormInterface $form;
            public function __construct(FormInterface $form) { $this->form = $form; }
            protected function createForm($type, $data = null, array $options = []): FormInterface { return $this->form; }
            protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse {
                return new RedirectResponse('/'.$route, $status);
            }
        };

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())->method('hashPassword')->willReturn('hashed_pw');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $em->expects($this->once())->method('flush');

        $request = new Request([], [
            'email' => 'test@example.com',
            'nickname' => 'tester',
            'plainPassword' => 'secret123'
        ]);

        $response = $controller->register($request, $passwordHasher, $em);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('app_login', $response->getTargetUrl());
    }
}
