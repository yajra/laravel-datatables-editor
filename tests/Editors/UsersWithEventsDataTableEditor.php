<?php

declare(strict_types=1);

namespace Yajra\DataTables\Tests\Editors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\Tests\Models\User;

class UsersWithEventsDataTableEditor extends DataTablesEditor
{
    protected $model = User::class;

    /**
     * Get create action validation rules.
     */
    public function createRules(): array
    {
        return [
            'email' => 'required|email',
            'name' => 'required',
        ];
    }

    public function created(Model $model, array $data): Model
    {
        return $model->setAttribute('created', 'it works!');
    }

    /**
     * Get edit action validation rules.
     */
    public function editRules(Model $model): array
    {
        return [
            'email' => 'sometimes|required|email|'.Rule::unique('users')->ignore($model->getKey()),
            'name' => 'sometimes|required',
        ];
    }

    public function updated(Model $model, array $data): Model
    {
        return $model->setAttribute('updated', 'it works!');
    }

    public function saved(Model $model, array $data): Model
    {
        return $model->setAttribute('saved', 'it works!');
    }

    /**
     * Get remove action validation rules.
     */
    public function removeRules(Model $model): array
    {
        return [];
    }
}
