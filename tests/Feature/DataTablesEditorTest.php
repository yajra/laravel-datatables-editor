<?php

namespace Yajra\DataTables\Tests\Feature;

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
        $this->expectExceptionMessage('Requested action (invalid) not supported!');

        $editor = new UsersDataTableEditor();
        request()->merge(['action' => 'invalid']);
        $editor->process(request());
    }

    #[Test]
    public function it_can_set_model_class_via_runtime()
    {
        $editor = new UsersDataTableEditor();
        $this->assertEquals(User::class, $editor->getModel());
        $editor->setModel(Post::class);
        $this->assertEquals(Post::class, $editor->getModel());
    }

    #[Test]
    public function it_can_set_model_instance_via_runtime()
    {
        $editor = new UsersDataTableEditor();
        $editor->setModel(new Post);
        $this->assertEquals(new Post, $editor->getModel());
    }
}
