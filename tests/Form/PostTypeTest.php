<?php

namespace App\Tests\Form;

use App\Entity\Post;
use App\Form\PostType;
use Symfony\Component\Form\Test\TypeTestCase;

class PostTypeTest extends TypeTestCase
{
    public function testFormContainsExpectedFields(): void
    {
        $form = $this->factory->create(PostType::class);

        $expected = ['title', 'description', 'type', 'tags'];
        foreach ($expected as $field) {
            $this->assertTrue($form->has($field), "Field '$field' is missing.");
        }
    }

    public function testFormSubmitsValidData(): void
    {
        $formData = [
            'title' => 'Test Post',
            'description' => 'This is a test post.',
            'type' => 'bug',
            'tags' => 'symfony,php',
        ];

        $model = new Post();
        $form = $this->factory->create(PostType::class, $model);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('Test Post', $model->getTitle());
        $this->assertSame('This is a test post.', $model->getDescription());
        $this->assertSame('bug', $model->getType());
        $this->assertSame('symfony,php', $model->getTags());
    }
}
