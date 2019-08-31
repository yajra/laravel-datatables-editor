<?php

namespace Yajra\DataTables;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class DataTablesEditor
{
    use ValidatesRequests;

    /**
     * Allowed dataTables editor actions.
     *
     * @var array
     */
    protected $actions = ['create', 'edit', 'remove'];

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model = null;

    /**
     * Indicates if all mass assignment is enabled on model.
     *
     * @var bool
     */
    protected $unguarded = false;

    /**
     * Process dataTables editor action request.
     *
     * @param Request $request
     * @return JsonResponse|mixed
     * @throws DataTablesEditorException
     */
    public function process(Request $request)
    {
        $action = $request->get('action');

        if (! in_array($action, $this->actions)) {
            throw new DataTablesEditorException('Requested action not supported!');
        }

        return $this->{$action}($request);
    }

    /**
     * Process create action request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $instance   = $this->resolveModel();
        $connection = $instance->getConnection();
        $affected   = [];
        $errors     = [];

        $connection->beginTransaction();
        foreach ($request->get('data') as $data) {
            $validator = $this->getValidationFactory()
                              ->make($data, $this->createRules(), $this->createMessages(), $this->attributes());
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error;
                }

                continue;
            }

            if (method_exists($this, 'creating')) {
                $data = $this->creating($instance, $data);
            }

            if (method_exists($this, 'saving')) {
                $data = $this->saving($instance, $data);
            }

            $instance->fill($data)->save();

            if (method_exists($this, 'created')) {
                $instance = $this->created($instance, $data);
            }

            if (method_exists($this, 'saved')) {
                $instance = $this->saved($instance, $data);
            }

            $instance->setAttribute('DT_RowId', $instance->getKey());
            $affected[] = $instance;
        }

        if (! $errors) {
            $connection->commit();
        } else {
            $connection->rollBack();
        }

        return $this->toJson($affected, $errors);
    }

    /**
     * Resolve model to used.
     *
     * @return Model|\Illuminate\Database\Eloquent\SoftDeletes
     */
    protected function resolveModel()
    {
        if (! $this->model instanceof Model) {
            $this->model = new $this->model;
        }

        $this->model->unguard($this->unguarded);

        return $this->model;
    }

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    abstract public function createRules();

    /**
     * Get create validation messages.
     *
     * @return array
     */
    protected function createMessages()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }

    /**
     * @param Validator $validator
     * @return array
     */
    protected function formatErrors(Validator $validator)
    {
        $errors = [];

        collect($validator->errors())->each(function ($error, $key) use (&$errors) {
            $errors[] = [
                'name'   => $key,
                'status' => $error[0],
            ];
        });

        return $errors;
    }

    /**
     * Display success data in dataTables editor format.
     *
     * @param array $data
     * @param array $errors
     * @return JsonResponse
     */
    protected function toJson(array $data, array $errors = [])
    {
        $response = ['data' => $data];
        if ($errors) {
            $response['fieldErrors'] = $errors;
        }

        return new JsonResponse($response, 200);
    }

    /**
     * Process edit action request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request)
    {
        $connection = $this->getBuilder()->getConnection();
        $affected   = [];
        $errors     = [];

        $connection->beginTransaction();
        foreach ($request->get('data') as $key => $data) {
            $model     = $this->getBuilder()->findOrFail($key);
            $validator = $this->getValidationFactory()
                              ->make($data, $this->editRules($model), $this->editMessages(), $this->attributes());
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error;
                }

                continue;
            }

            if (method_exists($this, 'updating')) {
                $data = $this->updating($model, $data);
            }

            if (method_exists($this, 'saving')) {
                $data = $this->saving($model, $data);
            }

            $model->fill($data)->save();

            if (method_exists($this, 'updated')) {
                $model = $this->updated($model, $data);
            }

            if (method_exists($this, 'saved')) {
                $model = $this->saved($model, $data);
            }

            $model->setAttribute('DT_RowId', $model->getKey());
            $affected[] = $model;
        }

        if (! $errors) {
            $connection->commit();
        } else {
            $connection->rollBack();
        }

        return $this->toJson($affected, $errors);
    }

    /**
     * Get elqouent builder of the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getBuilder()
    {
        $model = $this->resolveModel();

        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses($model))) {
            return $model->newQuery()->withTrashed();
        }

        return $model->newQuery();
    }

    /**
     * Get edit action validation rules.
     *
     * @param Model $model
     * @return array
     */
    abstract public function editRules(Model $model);

    /**
     * Get edit validation messages.
     *
     * @return array
     */
    protected function editMessages()
    {
        return [];
    }

    /**
     * Process remove action request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function remove(Request $request)
    {
        $connection = $this->getBuilder()->getConnection();
        $affected   = [];
        $errors     = [];

        $connection->beginTransaction();
        foreach ($request->get('data') as $key => $data) {
            $model     = $this->getBuilder()->findOrFail($key);
            $validator = $this->getValidationFactory()
                              ->make($data, $this->removeRules($model), $this->removeMessages(), $this->attributes());
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error['status'];
                }

                continue;
            }

            try {
                $deleted = clone $model;
                if (method_exists($this, 'deleting')) {
                    $this->deleting($model, $data);
                }

                $model->delete();

                if (method_exists($this, 'deleted')) {
                    $this->deleted($deleted, $data);
                }
            } catch (QueryException $exception) {
                $error = config('app.debug')
                    ? $exception->errorInfo[2]
                    : $this->removeExceptionMessage($exception, $model);

                $errors[] = $error;
            }

            $affected[] = $deleted;
        }

        if (! $errors) {
            $connection->commit();
        } else {
            $connection->rollBack();
        }

        $response = ['data' => $affected];
        if ($errors) {
            $response['error'] = implode("\n", $errors);
        }

        return new JsonResponse($response, 200);
    }

    /**
     * Get remove action validation rules.
     *
     * @param Model $model
     * @return array
     */
    abstract public function removeRules(Model $model);

    /**
     * Get remove validation messages.
     *
     * @return array
     */
    protected function removeMessages()
    {
        return [];
    }

    /**
     * Get remove query exception message.
     *
     * @param QueryException $exception
     * @param Model $model
     * @return string
     */
    protected function removeExceptionMessage(QueryException $exception, Model $model)
    {
        return "Record {$model->getKey()} is protected and cannot be deleted!";
    }

    /**
     * Get dataTables model.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the dataTables model on runtime.
     *
     * @param Model|string $model
     * @return DataTablesEditor
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set model unguard state.
     *
     * @param bool $state
     * @return $this
     */
    public function unguard($state = true)
    {
        $this->unguarded = $state;

        return $this;
    }

    /**
     * Display dataTables editor validation errors.
     *
     * @param Validator $validator
     * @return JsonResponse
     */
    protected function displayValidationErrors(Validator $validator)
    {
        $errors = $this->formatErrors($validator);

        return new JsonResponse([
            'data'        => [],
            'fieldErrors' => $errors,
        ]);
    }
}
