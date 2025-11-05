<?php

namespace App\Tests\Command;

use App\Command\CreateUserCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends TestCase
{
    public function testExecuteOutputsExpectedMessages(): void
    {
        $application = new Application();
        $application->add(new CreateUserCommand());

        $command = $application->find('app:create-user');
        $tester = new CommandTester($command);
        $tester->execute(['username' => 'test@example.com']);

        $output = $tester->getDisplay();

        $this->assertStringContainsString('User Creator', $output);
        $this->assertStringContainsString('create a user', $output);
        $this->assertStringContainsString('Username: test@example.com', $output);
        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testAliasesAreConfigured(): void
    {
        $command = new CreateUserCommand();
        $this->assertContains('app:add:user', $command->getAliases());
    }

    public function testCommandDescriptionIsSet(): void
    {
        $command = new CreateUserCommand();
        $this->assertSame('Creates a new user.', $command->getDescription());
    }
}
