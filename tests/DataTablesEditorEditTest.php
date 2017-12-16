<?php

namespace Yajra\DataTables\Tests;

class DataTablesEditorEditTest extends TestCase
{
    /** @test */
    public function it_can_process_edit_request()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data'   => [
                1 => [
                    'name'  => 'Jeffrey',
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

    /** @test */
    public function it_can_validate_invalid_inputs()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data'   => [
                1 => [
                    'name'  => '',
                    'email' => 'taylor',
                ],
            ],
        ]);

        $this->assertArrayHasKey('fieldErrors', $response->json());
        $errors = $response->json()['fieldErrors'];
        $this->assertArrayHasKey('name', $errors[0]);
        $this->assertArrayHasKey('status', $errors[0]);

        $this->assertEquals('email', $errors[0]['name']);
        $this->assertEquals('The email must be a valid email address.', $errors[0]['status']);

        $this->assertEquals('name', $errors[1]['name']);
        $this->assertEquals('The name field is required.', $errors[1]['status']);
    }

    /** @test */
    public function it_can_update_partial_data()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data'   => [
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

    /** @test */
    public function it_can_process_bulk_update_request()
    {
        $this->createUser();
        $this->createUser([
            'name'  => 'Jeffrey',
            'email' => 'jeffrey@laravel.com',
        ]);

        $response = $this->postJson('users', [
            'action' => 'edit',
            'data'   => [
                1 => [
                    'name'  => 'Arjay',
                ],
                2 => [
                    'name'  => 'Arjay',
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
