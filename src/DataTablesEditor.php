<?php

namespace Yajra\DataTables;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return app()->call([$this, $action]);
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
            $validator = $this->getValidationFactory()->make($data, $this->getCreateRules());
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error;
                };

                continue;
            }

            if (method_exists($this, 'creating')) {
                $data = $this->creating($instance, $data);
            }

            $model = $instance->newQuery()->create($data);
            $model->setAttribute('DT_RowId', $model->getKey());

            if (method_exists($this, 'created')) {
                $this->created($model, $data);
            }

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
     * Resolve model to used.
     *
     * @return Model
     */
    protected function resolveModel()
    {
        if ($this->model instanceof Model) {
            return $this->model;
        }

        return new $this->model;
    }

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    abstract public function getCreateRules();

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
        $instance   = $this->resolveModel();
        $connection = $instance->getConnection();
        $affected   = [];
        $errors     = [];

        $connection->beginTransaction();
        foreach ($request->get('data') as $key => $data) {
            $model     = $instance->newQuery()->find($key);
            $validator = $this->getValidationFactory()->make($data, $this->getEditRules($model));
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error;
                };

                continue;
            }

            if (method_exists($this, 'updating')) {
                $data = $this->updating($model, $data);
            }

            $model->update($data);

            if (method_exists($this, 'updated')) {
                $this->updated($model, $data);
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
     * Get edit action validation rules.
     *
     * @param Model $model
     * @return array
     */
    abstract public function getEditRules(Model $model);

    /**
     * Process remove action request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function remove(Request $request)
    {
        $model      = $this->resolveModel();
        $connection = $model->getConnection();
        $affected   = [];
        $errors     = [];

        $connection->beginTransaction();
        foreach ($request->get('data') as $key => $datum) {
            $instance  = $model->newQuery()->find($key);
            $validator = $this->getValidationFactory()->make($datum, $this->getRemoveRules($instance));
            if ($validator->fails()) {
                foreach ($this->formatErrors($validator) as $error) {
                    $errors[] = $error;
                };

                continue;
            }

            if (method_exists($this, 'deleting')) {
                app()->call([$this, 'deleting'], ['model' => $instance]);
            }

            $instance->delete();

            if (method_exists($this, 'deleted')) {
                app()->call([$this, 'deleted'], ['model' => $instance]);
            }

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
     * Get remove action validation rules.
     *
     * @param Model $model
     * @return array
     */
    abstract public function getRemoveRules(Model $model);

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
