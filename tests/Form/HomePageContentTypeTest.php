<?php

namespace App\Tests\Form;

use App\Entity\HomePageContent;
use App\Form\HomePageContentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

class HomePageContentTypeTest extends TypeTestCase
{
    public function testFormContainsExpectedFields(): void
    {
        $form = $this->factory->create(HomePageContentType::class);

        $this->assertTrue($form->has('title'), 'Form is missing "title" field.');
        $this->assertTrue($form->has('content'), 'Form is missing "content" field.');

        $this->assertInstanceOf(TextType::class, $form->get('title')->getConfig()->getType()->getInnerType());
        $this->assertInstanceOf(TextType::class, $form->get('content')->getConfig()->getType()->getInnerType());
    }

    public function testFormSubmitsValidData(): void
    {
        $formData = [
            'title' => 'Welcome to FeedbackCenter',
            'content' => 'This is a demo homepage content.',
        ];

        $model = new HomePageContent();
        $form = $this->factory->create(HomePageContentType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('Welcome to FeedbackCenter', $model->getTitle());
        $this->assertSame('This is a demo homepage content.', $model->getContent());
    }
}
    