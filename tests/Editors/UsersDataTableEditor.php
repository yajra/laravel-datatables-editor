<?php

namespace Yajra\DataTables\Tests\Editors;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\Tests\Models\User;

class UsersDataTableEditor extends DataTablesEditor
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

    /**
     * Get remove action validation rules.
     */
    public function removeRules(Model $model): array
    {
        return [];
    }
}
