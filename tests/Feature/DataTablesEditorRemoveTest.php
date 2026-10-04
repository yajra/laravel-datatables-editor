<?php

namespace Yajra\DataTables\Tests\Feature;

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\Tests\TestCase;

class DataTablesEditorRemoveTest extends TestCase
{
    #[Test]
    public function it_can_process_remove_request()
    {
        $this->createUser();

        $response = $this->postJson('users', [
            'action' => 'remove',
            'data' => [
                1 => [
                    'name' => 'Taylor',
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

    #[Test]
    public function it_denies_authenticated_remove_when_policy_rejects_the_row()
    {
        $this->createUser();
        $this->createUser([
            'name' => 'Jeffrey',
            'email' => 'jeffrey@laravel.com',
        ]);

        auth()->setUser(new GenericUser(['id' => 1]));
        Gate::define('delete', fn (GenericUser $user, $model): bool => $model->getKey() === 1);

        $response = $this->postJson('users', [
            'action' => 'remove',
            'data' => [
                2 => [
                    'name' => 'Jeffrey',
                    'email' => 'jeffrey@laravel.com',
                ],
            ],
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseHas('users', [
            'id' => 2,
            'name' => 'Jeffrey',
        ]);
    }

    #[Test]
    public function it_can_process_bulk_remove_request()
    {
        $this->createUser();
        $this->createUser([
            'name' => 'Jeffrey',
            'email' => 'jeffrey@laravel.com',
        ]);

        $this->assertDatabaseHas('users', ['id' => 1]);
        $this->assertDatabaseHas('users', ['id' => 2]);

        $response = $this->postJson('users', [
            'action' => 'remove',
            'data' => [
                1 => [
                    'name' => 'Taylor',
                    'email' => 'taylor@laravel.com',
                ],
                2 => [
                    'name' => 'Jeffrey',
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
