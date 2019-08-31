# Laravel DataTables Editor CHANGELOG

## [Unreleased]

## [v1.10.1] - 2019-08-31

- Fix creating and saving hooks are not saving the $data changes. [#36], credits to @karmendra.

## [v1.10.0] - 2019-08-27

- Add unguarded property to allow mass assignment on model.

## [v1.9.1] - 2019-08-24

- Fix bulk edit and remove.

## [v1.9.0] - 2019-08-24

- Add initial support for SoftDeletes.
- Fill model before firing updating event.
- Clone model before deleting to record affected models.

## [v1.8.1] - 2019-08-24

- Fill model before triggering the creating event.

## [v1.8.0] - 2019-06-06

- Get some new attributes when calling hooks [#27], credits to @aminprox
- Add model fluent getter and setter. [#29], fix [#24].
- Fix and added tests [#31].

## [v1.7.0] - 2019-02-27

- Add support for Laravel 5.8.

## [v1.6.1] - 2018-11-03

- Fix model instance.

## [v1.6.0] - 2018-11-03

- Add saving & saved event hook.

## [v1.5.0] - 2018-09-05

- Add support for Laravel 5.7.

## [v1.4.0] - 2018-08-15

- Add support for dataTables buttons package v4.

## [v1.3.0] - 2018-08-01

- Get custom attributes for validator errors [#14], credits to @karmendra
- Fix [#13]

## [v1.2.0] - 2018-06-27

- Add functions to override validation messages.

## [v1.1.4] - 2018-06-17

- Fix displaying of remove validation errors.

## [v1.1.3] - 2018-06-17

- Refactor remove query exception message.
- Allow remove error message customization.

## [v1.1.2] - 2018-06-13

- Fix displaying of remove validation error. [#9]
- Add remove error handler for constraint / query exception.

## [v1.1.1] - 2018-05-28

- Add missing key when remove validation failed.

## [v1.1.0] - 2018-02-11

- Add support for Laravel 5.6.
- Update license to 2018.

## [v1.0.0] - 2017-12-17

- First stable release.

### Features

- DataTables Editor CRUD actions supported.
- Inline editing.
- Bulk edit & delete function.
- CRUD validation.
- CRUD pre / post events hooks.
- Artisan command for DataTables Editor generation.

[Unreleased]: https://github.com/yajra/laravel-datatables-editor/compare/v1.8.0...master
[v1.8.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.7.0...v1.8.0
[v1.7.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.6.1...v1.7.0
[v1.6.1]: https://github.com/yajra/laravel-datatables-editor/compare/v1.6.0...v1.6.1
[v1.6.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.5.0...v1.6.0
[v1.5.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.4.0...v1.5.0
[v1.4.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.3.0...v1.4.0
[v1.3.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.2.0...v1.3.0
[v1.2.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.1.4...v1.2.0
[v1.1.4]: https://github.com/yajra/laravel-datatables-editor/compare/v1.1.3...v1.1.4
[v1.1.3]: https://github.com/yajra/laravel-datatables-editor/compare/v1.1.2...v1.1.3
[v1.1.2]: https://github.com/yajra/laravel-datatables-editor/compare/v1.1.1...v1.1.2
[v1.1.1]: https://github.com/yajra/laravel-datatables-editor/compare/v1.1.0...v1.1.1
[v1.1.0]: https://github.com/yajra/laravel-datatables-editor/compare/v1.0.0...v1.1.0
[v1.0.0]: https://github.com/yajra/laravel-datatables-editor/compare/master...v1.0.0

[#9]: https://github.com/yajra/laravel-datatables-editor/pull/9
[#14]: https://github.com/yajra/laravel-datatables-editor/pull/14
[#27]: https://github.com/yajra/laravel-datatables-editor/pull/27
[#29]: https://github.com/yajra/laravel-datatables-editor/pull/29
[#31]: https://github.com/yajra/laravel-datatables-editor/pull/31

[#13]: https://github.com/yajra/laravel-datatables-editor/issues/13
[#24]: https://github.com/yajra/laravel-datatables-editor/issues/24
