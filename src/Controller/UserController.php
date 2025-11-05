<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserService $userService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'], $data['nickname'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        try {
            $userService->createUser($data);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        return $this->json(['status' => 'User registered'], 201);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Missing email or password'], 400);
        }

        $user = $userRepository->findOneBy(['email' => $data['email']]);
        if (!$user) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $jwtManager->create($user);

        return $this->json(['token' => $token], 200);
    }

    #[Route('/api/profile', name: 'api_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException('You must be logged in');
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nickname' => $user->getNickname(),
        ]);
    }

    // Wylogowanie
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json(['status' => 'Logged out'], 200);
    }

    // Usuwanie konta
    #[Route('/api/users/delete', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException('You must be logged in');
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['status' => 'User deleted'], 200);
    }
    
    // Edycja użytkownika
    #[Route('/api/users/edit', name: 'user_edit', methods: ['PUT'])]
    public function editUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        if (isset($data['nickname'])) {
            $user->setNickname($data['nickname']);
        }

        $em->flush();

        return $this->json(['status' => 'User updated']);
    }
}
