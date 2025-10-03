<?php

namespace Yajra\DataTables\Tests\Feature;

use Illuminate\Routing\Router;
use PHPUnit\Framework\Attributes\Test;
use Yajra\DataTables\DataTablesEditorException;
use Yajra\DataTables\Tests\Editors\UsersDataTableEditor;
use Yajra\DataTables\Tests\Models\Post;
use Yajra\DataTables\Tests\Models\User;
use Yajra\DataTables\Tests\TestCase;

class DataTablesEditorTest extends TestCase
{
    #[Test]
    public function it_throws_exception_on_invalid_action()
    {
        $this->expectException(DataTablesEditorException::class);
        $this->expectExceptionMessage('Invalid action requested!');

        $editor = new UsersDataTableEditor;
        request()->merge(['action' => 'invalid']);
        $editor->process(request());
    }

    #[Test]
    public function it_can_set_model_class_via_runtime()
    {
        $editor = new UsersDataTableEditor;
        $this->assertEquals(User::class, $editor->getModel());
        $editor->setModel(Post::class);
        $this->assertEquals(Post::class, $editor->getModel());
    }

    #[Test]
    public function it_can_set_model_instance_via_runtime()
    {
        $editor = new UsersDataTableEditor;
        $editor->setModel(new Post);
        $this->assertEquals(new Post, $editor->getModel());
    }

    #[Test]
    public function it_can_be_used_as_route_action(): void
    {
        /** @var Router $router */
        $router = $this->app['router'];
        $router->post('editor-as-route-action', UsersDataTableEditor::class);

        $this->postJson('editor-as-route-action', [
            'action' => 'create',
            'data' => [
                [
                    'name' => 'New User',
                    'email' => 'newuser@email.test',
                ],
            ],
        ])
            ->assertOk()
            ->assertJsonStructure(['action', 'data'])
            ->assertJsonPath('action', 'create');
    }
}
