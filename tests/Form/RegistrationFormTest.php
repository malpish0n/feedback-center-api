<?php

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\RegistrationForm;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormTest extends TypeTestCase
{
    public function testFormContainsExpectedFields(): void
    {
        $form = $this->factory->create(RegistrationForm::class);

        $expectedFields = ['email', 'nickname', 'plainPassword'];
        foreach ($expectedFields as $field) {
            $this->assertTrue($form->has($field), "Missing field: $field");
        }
    }

    public function testPlainPasswordFieldConfiguration(): void
    {
        $form = $this->factory->create(RegistrationForm::class);
        $plainPasswordConfig = $form->get('plainPassword')->getConfig();

        $this->assertSame(PasswordType::class, $plainPasswordConfig->getType()->getInnerType()::class);
        $this->assertFalse($plainPasswordConfig->getOption('mapped'));
        $this->assertSame('Password', $plainPasswordConfig->getOption('label'));
        $this->assertSame('new-password', $plainPasswordConfig->getOption('attr')['autocomplete']);
    }

    public function testFormSubmissionMapsToUserEntity(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'nickname' => 'tester',
            'plainPassword' => 'secret123',
        ];

        $user = new User();
        $form = $this->factory->create(RegistrationForm::class, $user);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('tester', $user->getNickname());

        $this->assertObjectNotHasProperty('plainPassword', $user);
    }

}
