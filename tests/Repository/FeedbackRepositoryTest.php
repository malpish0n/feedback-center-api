<?php

namespace App\Tests\Repository;

use App\Entity\Feedback;
use App\Repository\FeedbackRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class FeedbackRepositoryTest extends TestCase
{
    public function testRepositoryCanBeInstantiated(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new FeedbackRepository($registry);

        $this->assertInstanceOf(FeedbackRepository::class, $repository);
    }

        public function testRepositoryUsesFeedbackEntity(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $metadata = new ClassMetadata(Feedback::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($entityManager);
        $entityManager->method('getClassMetadata')->willReturn($metadata);

        $repository = new FeedbackRepository($registry);

        $this->assertSame(Feedback::class, $repository->getClassName());
    }
}
