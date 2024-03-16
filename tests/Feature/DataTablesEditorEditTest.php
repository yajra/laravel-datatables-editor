<?php

namespace Yajra\DataTables\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\Tests\TestCase;

class DataTablesEditorEditTest extends TestCase
{
    #[Test]
    public function it_can_process_edit_request()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data' => [
                1 => [
                    'name' => 'Jeffrey',
                    'email' => 'jefrrey@laravel.com',
                ],
            ],
        ]);

        $data = $response->json()['data'][0];

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Jeffrey', $data['name']);
        $this->assertEquals('jefrrey@laravel.com', $data['email']);
    }

    #[Test]
    public function it_allows_updated_callback_and_returns_the_modified_model()
    {
        $this->createUser();

        $response = $this->postJson('usersWithEvents', [
            'action' => 'edit',
            'data' => [
                1 => [
                    'name' => 'Jeffrey',
                    'email' => 'jefrrey@laravel.com',
                ],
            ],
        ]);

        $data = $response->json()['data'][0];

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Jeffrey', $data['name']);
        $this->assertEquals('jefrrey@laravel.com', $data['email']);
        $this->assertEquals('it works!', $data['updated']);
        $this->assertEquals('it works!', $data['saved']);
    }

    #[Test]
    public function it_can_validate_invalid_inputs()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data' => [
                1 => [
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

    #[Test]
    public function it_can_update_partial_data()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data' => [
                1 => [
                    'name' => 'Jeffrey',
                ],
            ],
        ]);

        $data = $response->json()['data'][0];

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Jeffrey', $data['name']);
    }

    #[Test]
    public function it_can_process_bulk_update_request()
    {
        $this->createUser();
        $this->createUser([
            'name' => 'Jeffrey',
            'email' => 'jeffrey@laravel.com',
        ]);

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data' => [
                1 => [
                    'name' => 'Arjay',
                ],
                2 => [
                    'name' => 'Arjay',
                ],
            ],
        ]);

        $data = $response->json()['data'][0];
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Arjay', $data['name']);
        $this->assertEquals('taylor@laravel.com', $data['email']);

        $data = $response->json()['data'][1];
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(2, $data['id']);
        $this->assertEquals('Arjay', $data['name']);
        $this->assertEquals('jeffrey@laravel.com', $data['email']);
    }
}
