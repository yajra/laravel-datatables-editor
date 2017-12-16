<?php

namespace Yajra\DataTables\Tests;

use Yajra\DataTables\DataTablesEditorException;
use Yajra\DataTables\Tests\Editors\UsersDataTableEditor;

class DataTablesEditorTest extends TestCase
{
    /** @test */
    public function it_throws_exception_on_invalid_action()
    {
        $this->expectException(DataTablesEditorException::class);
        $this->expectExceptionMessage('Requested action not supported!');

        $editor = new UsersDataTableEditor();
        request()->merge(['action' => 'invalid']);
        $editor->process(request());
    }
}
