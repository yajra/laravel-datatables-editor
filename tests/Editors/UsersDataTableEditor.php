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
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'email' => 'required|email',
            'name' => 'required',
        ];
    }

    /**
     * Get edit action validation rules.
     *
     * @return array
     */
    public function editRules(Model $model)
    {
        return [
            'email' => 'sometimes|required|email|'.Rule::unique('users')->ignore($model->getKey()),
            'name' => 'sometimes|required',
        ];
    }

    /**
     * Get remove action validation rules.
     *
     * @return array
     */
    public function removeRules(Model $model)
    {
        return [];
    }
}
