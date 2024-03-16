<?php

namespace Yajra\DataTables\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\Tests\TestCase;

class DataTablesEditorCreateTest extends TestCase
{
    #[Test]
    public function it_can_process_create_request()
    {
        $response = $this->postJson('users', [
            'action' => 'create',
            'data' => [
                0 => [
                    'name' => 'Taylor',
                    'email' => 'taylor@laravel.com',
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', ['id' => 1]);

        $data = $response->json()['data'][0];
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Taylor', $data['name']);
        $this->assertEquals('taylor@laravel.com', $data['email']);
    }

    #[Test]
    public function it_allows_created_callback_and_returns_the_modified_model()
    {
        $response = $this->postJson('usersWithEvents', [
            'action' => 'create',
            'data' => [
                0 => [
                    'name' => 'Taylor',
                    'email' => 'taylor@laravel.com',
                ],
            ],
        ]);

        $this->assertDatabaseHas('users', ['id' => 1]);

        $data = $response->json()['data'][0];
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Taylor', $data['name']);
        $this->assertEquals('taylor@laravel.com', $data['email']);
        $this->assertEquals('it works!', $data['created']);
        $this->assertEquals('it works!', $data['saved']);
    }

    #[Test]
    public function it_can_validate_invalid_inputs()
    {
        $response = $this->postJson('users', [
            'action' => 'create',
            'data' => [
                [
                    'name' => '',
                    'email' => 'taylor',
                ],
            ],
        ]);

        $this->assertArrayHasKey('fieldErrors', $response->json());
        $errors = $response->json()['fieldErrors'];
        $this->assertArrayHasKey('name', $errors[0]);
        $this->assertArrayHasKey('status', $errors[0]);

        $this->assertEquals('email', $errors[0]['name']);
        $this->assertEquals('The email field must be a valid email address.', $errors[0]['status']);

        $this->assertEquals('name', $errors[1]['name']);
        $this->assertEquals('The name field is required.', $errors[1]['status']);
    }
}
