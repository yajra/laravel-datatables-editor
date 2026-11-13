<?php

namespace Yajra\DataTables\Tests\Feature;

use Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Yajra\DataTables\Tests\Editors\UsersDataTableEditor;
use Yajra\DataTables\Tests\Models\User;
use Yajra\DataTables\Tests\TestCase;

class DataTablesEditorTransactionTest extends TestCase
{
    public static function actions(): iterable
    {
        foreach (['create', 'edit', 'remove', 'restore', 'forceDelete'] as $action) {
            foreach ([false, true] as $outer) {
                yield $action.($outer ? ' with outer transaction' : ' without outer transaction') => [$action, $outer];
            }
        }
    }

    #[DataProvider('actions')]
    public function test_hook_failures_roll_back_only_the_action(string $action, bool $outer): void
    {
        $this->assertActionResult($action, $outer, 'exception');
    }

    #[DataProvider('actions')]
    public function test_errors_roll_back_only_the_action(string $action, bool $outer): void
    {
        $this->assertActionResult($action, $outer, 'error');
    }

    #[DataProvider('actions')]
    public function test_validation_failures_return_no_rolled_back_models(string $action, bool $outer): void
    {
        $this->assertActionResult($action, $outer, 'validation');
    }

    public static function existingRecordActions(): iterable
    {
        foreach (self::actions() as $name => $arguments) {
            if ($arguments[0] !== 'create') {
                yield $name => $arguments;
            }
        }
    }

    #[DataProvider('existingRecordActions')]
    public function test_missing_records_roll_back_only_the_action(string $action, bool $outer): void
    {
        $this->assertActionResult($action, $outer, 'missing');
    }

    #[DataProvider('actions')]
    public function test_successful_actions_preserve_the_callers_transaction(string $action, bool $outer): void
    {
        $this->assertActionResult($action, $outer, 'success');
    }

    public static function removeActions(): iterable
    {
        foreach (self::actions() as $name => $arguments) {
            if (in_array($arguments[0], ['remove', 'forceDelete'])) {
                yield $name => $arguments;
            }
        }
    }

    #[DataProvider('removeActions')]
    public function test_handled_query_errors_return_no_rolled_back_models(string $action, bool $outer): void
    {
        $this->assertActionResult($action, $outer, 'query');
    }

    private function assertActionResult(string $action, bool $outer, string $failure): void
    {
        $connection = (new User)->getConnection();
        $connection->rollBack(0);
        $this->migrateDatabase();
        $connection->getSchemaBuilder()->table('users', fn ($table) => $table->softDeletes());
        $first = $this->createUser();
        $second = $this->createUser(['name' => 'Second', 'email' => 'second@example.com']);
        if ($action === 'restore') {
            $connection->table('users')->update(['deleted_at' => now()]);
        }
        if ($outer) {
            $connection->beginTransaction();
        }
        $sentinel = $this->createUser(['name' => 'Outer', 'email' => 'outer@example.com']);
        $before = $connection->table('users')->orderBy('id')->get()->toArray();
        $level = $connection->transactionLevel();
        $editor = new class extends UsersDataTableEditor
        {
            public function createRules(): array
            {
                return ['name' => 'required'];
            }

            public function editRules(Model $model): array
            {
                return $this->createRules();
            }

            public function removeRules(Model $model): array
            {
                return $this->createRules();
            }
        };
        $editor->setModel(new class extends User
        {
            use SoftDeletes;

            protected $table = 'users';
        });
        $event = match ($action) {
            'create' => 'creating',
            'edit' => 'saving',
            'restore' => 'restoring',
            default => 'deleting',
        };
        $modelClass = $editor->getModel()::class;
        $modelClass::$event(function (Model $model) use ($failure): void {
            if ($model->name === 'Second' && $failure === 'query') {
                throw new QueryException('sqlite', 'delete from users', [], new RuntimeException('Delete failed'));
            }
            if ($model->name === 'Second' && in_array($failure, ['exception', 'error'])) {
                throw $failure === 'error' ? new Error('Hook failed') : new RuntimeException('Hook failed');
            }
        });
        $request = new Request([
            'action' => $action,
            'data' => [
                $first->id => ['name' => 'Changed', 'email' => 'changed@example.com'],
                $failure === 'missing' ? 999 : $second->id => [
                    'name' => $failure === 'validation' ? '' : 'Second',
                    'email' => 'second@example.com',
                ],
            ],
        ]);
        try {
            $response = $editor->process($request);
            $status = $failure === 'success' ? 200 : ($failure === 'validation' && ! in_array($action, ['remove', 'forceDelete']) ? 422 : 400);
            $this->assertSame($status, $response->getStatusCode());
            $this->assertSame($level, $connection->transactionLevel());
            if ($failure === 'success') {
                $this->assertCount(2, $response->getData(true)['data']);
                match ($action) {
                    'create' => $this->assertDatabaseCount('users', 5),
                    'edit' => $this->assertDatabaseHas('users', ['id' => $first->id, 'name' => 'Changed']),
                    'restore' => $this->assertDatabaseHas('users', ['id' => $first->id, 'deleted_at' => null]),
                    'remove' => $this->assertSoftDeleted('users', ['id' => $first->id]),
                    'forceDelete' => $this->assertDatabaseMissing('users', ['id' => $first->id]),
                };
            } else {
                $this->assertEquals($before, $connection->table('users')->orderBy('id')->get()->toArray());
                $this->assertSame([], $response->getData(true)['data']);
                $this->assertNotEmpty($response->getData(true)[$status === 422 ? 'fieldErrors' : 'error']);
            }
            $this->assertDatabaseHas('users', ['id' => $sentinel->id]);
            if ($outer) {
                $connection->rollBack();
                $this->assertDatabaseMissing('users', ['id' => $sentinel->id]);
            }
        } finally {
            $modelClass::flushEventListeners();
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack(0);
            }
        }
    }
}
