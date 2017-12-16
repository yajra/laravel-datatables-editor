<?php

namespace Yajra\DataTables\Tests;

class DataTablesEditorRemoveTest extends TestCase
{
    /** @test */
    public function it_can_process_remove_request()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'remove',
            'data'   => [
                1 => [
                    'name'  => 'Taylor',
                    'email' => 'taylor@laravel.com',
                ],
            ],
        ]);

        $data = $response->json()['data'][0];

        $this->assertDatabaseMissing('users', ['id' => 1]);

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Taylor', $data['name']);
        $this->assertEquals('taylor@laravel.com', $data['email']);
    }

    /** @test */
    public function it_can_process_bulk_remove_request()
    {
        $this->createUser();
        $this->createUser([
            'name'  => 'Jeffrey',
            'email' => 'jeffrey@laravel.com',
        ]);

        $this->assertDatabaseHas('users', ['id' => 1]);
        $this->assertDatabaseHas('users', ['id' => 2]);

        $response = $this->postJson('users', [
            'action' => 'remove',
            'data'   => [
                1 => [
                    'name'  => 'Taylor',
                    'email' => 'taylor@laravel.com',
                ],
                2 => [
                    'name'  => 'Jeffrey',
                    'email' => 'jefrrey@laravel.com',
                ],
            ],
        ]);


        $this->assertDatabaseMissing('users', ['id' => 1]);
        $this->assertDatabaseMissing('users', ['id' => 2]);

        $data = $response->json()['data'][0];
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Taylor', $data['name']);
        $this->assertEquals('taylor@laravel.com', $data['email']);

        $data = $response->json()['data'][1];
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(2, $data['id']);
        $this->assertEquals('Jeffrey', $data['name']);
        $this->assertEquals('jeffrey@laravel.com', $data['email']);
    }
}
