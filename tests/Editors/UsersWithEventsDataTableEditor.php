<?php

namespace Yajra\DataTables\Tests\Editors;

use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Tests\Models\User;

class UsersWithEventsDataTableEditor extends DataTablesEditor
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
            'name'  => 'required',
        ];
    }

    public function created($model, $data)
    {
        return $model->setAttribute('created', 'it works!');
    }

    /**
     * Get edit action validation rules.
     *
     * @param Model $model
     * @return array
     */
    public function editRules(Model $model)
    {
        return [
            'email' => 'sometimes|required|email|' . Rule::unique('users')->ignore($model->getKey()),
            'name'  => 'sometimes|required',
        ];
    }

    public function updated($model, $data)
    {
        return $model->setAttribute('updated', 'it works!');
    }

    public function saved($model, $data)
    {
        return $model->setAttribute('saved', 'it works!');
    }

    /**
     * Get remove action validation rules.
     *
     * @param Model $model
     * @return array
     */
    public function removeRules(Model $model)
    {
        return [];
    }
}
