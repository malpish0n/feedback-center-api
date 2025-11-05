<?php

namespace App\Tests\Form;

use App\Form\UserType;
use Symfony\Component\Form\Test\TypeTestCase;

class UserTypeTest extends TypeTestCase
{
    public function testFormBuildsWithExpectedFields(): void
    {
        $form = $this->factory->create(UserType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('nickname'));
        $this->assertTrue($form->has('password'));
    }
}
