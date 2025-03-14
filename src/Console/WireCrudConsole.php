<?php

namespace Manusiakemos\Wirecrud\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class WireCrudConsole extends Command
{
    protected $signature = 'wirecrud:make';

    protected $description = 'Generate Livewire 3 CRUD From Existing Tables';

    protected bool $uuid;

    protected Collection $fields;

    protected string $primaryKey;

    protected string $className;

    protected string $classNameCamel;

    protected string $classNameSpace;

    protected string $classNameSnake;

    protected string $table;

    protected string $classNameSlug;

    protected bool $hasUpload;

    protected bool $isModal;

    protected function getStub($filename): string
    {
        $path = base_path('stubs/vendor/manusiakemos/wirecrud/' . $filename);
        if (File::exists($path)) {
            return $path;
        }

        return __DIR__ . '/stubs/' . $filename;
    }

    public function handle(): bool
    {
        $tableName = text(label: 'table name on database:', required: true);
        $this->table = $tableName;
        $columns = Schema::getColumnListing($tableName);
        if (count($columns) == 0) {
            $this->error('table not found');
            return false;
        }

        $className = text(label: 'class name:', default: Str::studly($tableName), required: true);
        $pageType = select(
            label: 'Form Page Type',
            options: ['modal', 'page'],
            default: 'modal'
        );
        $generateModel = select(
            label: 'Generate Model',
            options: ['yes', 'no'],
            default: config('wirecrud.model') ? 'yes' : 'no'
        );
        $generateLivewire = select(
            label: 'Generate Livewire',
            options: ['yes', 'no'],
            default: config('wirecrud.livewire') ? 'yes' : 'no'
        );
        $generateService = select(
            label: 'Generate Service',
            options: ['yes', 'no'],
            default: config('wirecrud.service') ? 'yes' : 'no'
        );
        $generateView = select(
            label: 'Generate View',
            options: ['yes', 'no'],
            default: config('wirecrud.view') ? 'yes' : 'no'
        );
        $generateApi = select(
            label: 'Generate Api',
            options: ['yes', 'no'],
            default: config('wirecrud.api') ? 'yes' : 'no'
        );
        $generateSortable = select(
            label: 'Generate Sortable',
            options: ['yes', 'no'],
            default: 'no'
        );
        $fields = collect();
        $hasUpload = false;
        foreach ($columns as $column) {
            $labelColumnFk = false;
            $type = Schema::getColumnType($tableName, $column);
            $keyType = false;
            if (Str::contains(haystack: $column, needles: 'id')) {
                $keyType = select(
                    label: 'choose ' . $column . ' key type',
                    options: ['primary', 'foreign', 'false'],
                    default: 'primary'
                );
                if ($keyType == 'foreign') {
                    $type = 'select';
                    $labelColumnFk = text(label: $column . ' label column for select component', default: 'name', required: true);
                } elseif ($keyType == 'primary') {
                    //ask to use uuid
                    $uuid = select(
                        label: 'ID is uuid',
                        options: ['yes', 'no'],
                        default: config('wirecrud.uuid') ? 'yes' : 'no',
                    );
                    $this->uuid = $uuid === 'yes';
                    $type = 'invisible';
                }
            }
            $isUpload = 'no';
            if (Str::contains(haystack: $column, needles: 'image') || Str::contains(haystack: $column, needles: 'file')) {
                $isUpload = select(
                    label: 'Is ' . $column . ' a file upload',
                    options: ['yes', 'no'],
                    default: 'yes'
                );
                if ($isUpload == 'yes') {
                    $hasUpload = true;
                    $type = 'file';
                }
            }

            if (!Str::contains(haystack: $column, needles: '_at')) {
                if (Str::contains(haystack: $column, needles: 'id') || Str::contains(haystack: $column, needles: '_by') && $keyType == 'foreign') {
                    $label = Str::replace(search: 'id', replace: '', subject: $column);
                } else {
                    $label = Str::replace('_', ' ', Str::snake($column, ' '));
                }

                $fields->push([
                    'key_type' => $keyType,
                    'label' => $label,
                    'column' => $column,
                    'type' => $type,
                    'is_upload' => $isUpload == 'yes',
                    'label_column' => $labelColumnFk,
                ]);
            }
        }

        Log::debug('fields', $fields->toArray());
        $this->fields = $fields;
        $this->className = $className;
        $this->classNameSpace = Str::of($className)->headline();
        $this->classNameSnake = Str::of($className)->snake();
        $this->classNameCamel = Str::of($this->classNameSpace)->camel();
        $this->classNameSlug = Str::of($this->classNameSpace)->slug();
        $this->table = $tableName;
        $this->isModal = $pageType == 'modal';
        $this->hasUpload = $hasUpload;

        $this->setPrimaryKey();

        if ($generateModel == 'yes') {
            $this->generateModel();
        }
        $this->generateRepository();
        $this->generateSeeder();
        $this->generateFactory();
        if ($generateService == 'yes') {
            $this->generateService();
        }
        if ($generateLivewire) {
            $this->generateLivewire();
        }
        if ($generateView) {
            $this->generateView();
        }
        if ($generateApi == 'yes') {
            $this->generateApi();
        }
        if ($generateSortable == 'yes') {
            $this->generateSortable();
        }

        $this->call('ide-helper:models', ['-W' => 'true']);
        $this->info('success');

        return true;
    }

    private function setPrimaryKey(): void
    {
        $pk = $this->fields->where('key_type', 'primary')->first();
        $this->primaryKey = $pk['column'];
    }

    /*
    |--------------------------------------------------------------------------
    | Generated Model class
    |--------------------------------------------------------------------------
    */
    private function generateModel(): void
    {
        $getBooleanColumns = $this->fields->where('type', 'tinyint')->pluck('column')->toArray();
        $castsString = 'protected $casts = [';
        foreach ($getBooleanColumns as $column) {
            $castsString .= "'$column' => 'boolean',";
        }
        $castsString .= '];';   
        $stubTemplate = [
            '{@casts}',
            '{@className}',
            '{@table}',
            '{@primaryKey}',
            '{@useUUid}',
            '{@uuid}',
        ];

        $stubReplaceTemplate = [
            $castsString,
            $this->className,
            $this->table,
            $this->primaryKey,
            $this->uuid ? 'use Illuminate\Database\Eloquent\Concerns\HasUuids;' : '',
        ];
        $stub_template = file_get_contents($this->getStub('model.stub'));
        $modelTemplate = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);

        $path = app_path('/Models');
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);

        file_put_contents(app_path("Models/$this->className.php"), $modelTemplate);
    }

    /*
    |--------------------------------------------------------------------------
    | generate repository file
    |--------------------------------------------------------------------------
    */
    public function generateRepository(): void
    {
        $stubTemplate = [
            '@{className}',
        ];
        $stubReplaceTemplate = [
            $this->className,
        ];
        $stub_template = file_get_contents($this->getStub('repository.stub'));
        $modelTemplate = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);

        $path = app_path('/Repositories/' . $this->className);
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);

        file_put_contents(app_path("Repositories/$this->className/{$this->className}Repository.php"), $modelTemplate);

        //implement class
        $stub_template = file_get_contents($this->getStub('repository-implement.stub'));
        $modelTemplate = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);

        $path = app_path('/Repositories');
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);

        file_put_contents(app_path("Repositories/$this->className/{$this->className}RepositoryImplement.php"), $modelTemplate);

    }

    /*
    |--------------------------------------------------------------------------
    | generate service file
    |--------------------------------------------------------------------------
    */
    public function generateService(): void
    {
        $stubTemplate = [
            '@{className}',
        ];
        $stubReplaceTemplate = [
            $this->className,
        ];
        $stub_template = file_get_contents($this->getStub('service.stub'));
        $modelTemplate = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);

        $path = app_path('/Services/' . $this->className);
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);

        file_put_contents(app_path("Services/$this->className/{$this->className}Service.php"), $modelTemplate);

        //implement class
        $stub_template = file_get_contents($this->getStub('service-implement.stub'));
        $modelTemplate = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);

        $path = app_path('/Services');
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);

        file_put_contents(app_path("Services/$this->className/{$this->className}ServiceImplement.php"), $modelTemplate);

    }

    /*
    |--------------------------------------------------------------------------
    | Generated Livewire class
    |--------------------------------------------------------------------------
    */
    private function generateLivewire(): void
    {

        if ($this->hasUpload) {
            $useTraitFile = View::make('wirecrud::string-builder._use-trait')->render();
            $traitFile = 'use UploadFileTrait;';
            $storeUpload = View::make('wirecrud::string-builder._store-upload', [
                'classNameSlug' => $this->classNameSlug,
                'classNameSnake' => $this->classNameSnake,
                'uploadColumn' => $this->fields->where('type', '=', 'file')->first(),
            ])->render();
            $updateUpload = View::make('wirecrud::string-builder._update-upload', [
                'classNameSlug' => $this->classNameSlug,
                'classNameSnake' => $this->classNameSnake,
                'uploadColumn' => $this->fields->where('type', '=', 'file')->first(),
            ])->render();

            $deleteUpload = View::make('wirecrud::string-builder._delete-upload', [
                'classNameSlug' => $this->classNameSlug,
                'classNameSnake' => $this->classNameSnake,
                'uploadColumn' => $this->fields->where('type', '=', 'file')->first(),
            ])->render();
        } else {
            $useTraitFile = '';
            $traitFile = '';
            $storeUpload = '';
            $updateUpload = '';
            $deleteUpload = '';
        }

        $options = View::make('wirecrud::string-builder._options', [
            'fields' => $this->fields->where('key_type', '=', 'foreign')->toArray(),
        ])->render();

        $handleRequest = View::make('wirecrud::string-builder._helper_handle_request', [
            'fields' => $this->fields,
            'classNameLower' => $this->classNameSnake,
        ])->render();

        $validate = View::make('wirecrud::string-builder._helper_validate_generator', [
            'hasUpload' => $this->hasUpload,
            'field_validate' => $this->fields->where('key_type', '<>', 'primary')->where('type', '<>', 'file'),
            'classNameLower' => $this->classNameSnake,
        ])->render();

        $columns = View::make('wirecrud::string-builder._helper_columns', [
            'fields' => $this->fields->where('key_type', '<>', 'primary'),
            'classNameLower' => $this->classNameSnake,
        ])->render();

        $stubTemplate = [
            '{@pk}',
            '{@deleteUpload}',
            '{@updateUpload}',
            '{@storeUpload}',
            '{@options}',
            '{@useTraitFile}',
            '{@traitFile}',
            '{@primaryKey}',
            '{@handleRequest}',
            '{@validate}',
            '{@columns}',
            '{@className}',
            '{@classNameLower}',
            '{@classNameCamel}',
            '{@classNameSnake}',
            '{@classNameSpace}',
            '{@classNameSlug}',
        ];
        $stubReplaceTemplate = [
            $this->primaryKey,
            $deleteUpload,
            $updateUpload,
            $storeUpload,
            $options,
            $useTraitFile,
            $traitFile,
            $this->primaryKey,
            $handleRequest,
            $validate,
            $columns,
            $this->className,
            $this->classNameSnake,
            $this->classNameCamel,
            $this->classNameSnake,
            $this->classNameSpace,
            $this->classNameSlug,
        ];

        if ($this->isModal) {
            $templateFile = file_get_contents($this->getStub('livewire-modal.stub'));
        } else {
            $templateFile = file_get_contents($this->getStub('livewire.stub'));
        }
        $template = str_replace($stubTemplate, $stubReplaceTemplate, $templateFile);
        $path = app_path("/Livewire/$this->className");
        File::isDirectory($path) or File::makeDirectory($path, 0775, true, true);
        $this->generateEntity();
        file_put_contents(app_path("/Livewire/$this->className/{$this->className}Page.php"), $template);
        if (!$this->isModal) {
            $templateFile = file_get_contents($this->getStub('livewire-form.stub'));
            $template = str_replace($stubTemplate, $stubReplaceTemplate, $templateFile);
            file_put_contents(app_path("/Livewire/$this->className/{$this->className}FormPage.php"), $template);
        }

        $templateFile = file_get_contents($this->getStub('table-class.stub'));
        $template = str_replace($stubTemplate, $stubReplaceTemplate, $templateFile);
        file_put_contents(app_path("/Livewire/$this->className/{$this->className}Table.php"), $template);
    }

    /*
    |--------------------------------------------------------------------------
    | Generated Entity class
    |--------------------------------------------------------------------------
    */
    private function generateEntity(): void
    {
        $generatedProps = View::make('wirecrud::string-builder._helper_props', [
            'fields' => $this->fields,
        ])->render();

        $templateFile = file_get_contents($this->getStub('entity.stub'));
        $stubTemplate = [
            '{@generatedProps}',
            '{@className}',
            '{@classNameSnake}',
        ];
        $stubReplaceTemplate = [
            $generatedProps,
            $this->className,
            $this->classNameSnake,
        ];
        $template = str_replace($stubTemplate, $stubReplaceTemplate, $templateFile);
        file_put_contents(app_path("/Livewire/$this->className/{$this->className}Entity.php"), $template);
    }

    /*
     |--------------------------------------------------------------------------
     | Generate View
     |--------------------------------------------------------------------------
     |
     | auto generate view file function
     | main view, form view, table action view, confirm box view
     |
     */
    private function generateView(): void
    {
        $this->generateViewPage();
        $this->generateFormView();
        $this->generateActionView();
        $this->generateNavigationView();
    }

    private function generateViewPage(): void
    {
        $stubTemplate = [
            '{@primaryKey}',
            '{@className}',
            '{@classNameSlug}',
            '{@classNameLower}',
            '{@classNameSpace}',
        ];
        $stubReplaceTemplate = [
            $this->primaryKey,
            $this->className,
            $this->classNameSlug,
            $this->classNameCamel,
            $this->classNameSpace,
        ];
        if ($this->isModal) {
            $stub_template = file_get_contents($this->getStub('page-modal.stub'));
        } else {
            $stub_template = file_get_contents($this->getStub('page.stub'));
        }

        $template = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);
        $path = resource_path("views/livewire/$this->classNameSlug");
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);
        $pathToWrite = resource_path("views/livewire/$this->classNameSlug/$this->classNameSlug-page.blade.php");
        file_put_contents($pathToWrite, $template);
    }

    public function generateFormView(): void
    {
        $forms = View::make('wirecrud::string-builder._helper_form', [
            'fields' => $this->fields->where('key_type', '<>', 'primary'),
            'model' => $this->classNameSnake,
        ])->render();
        $forms = str_replace('wirex', 'x', $forms);

        $search = [
            '{@pk}',
            '{@className}',
            '{@tableName}',
            '{@forms}',
            '{@classNameSlug}',
            '{@classNameLower}',
            '{@classNameSpace}',
        ];
        $replace = [
            $this->primaryKey,
            $this->className,
            $this->table,
            $forms,
            $this->classNameSlug,
            $this->classNameSnake,
            $this->classNameSpace,
        ];

        if ($this->isModal) {
            $stub_template = file_get_contents($this->getStub('form-modal.stub'));
            $pathToWrite = resource_path("views/livewire/$this->classNameSlug/_$this->classNameSlug-form.blade.php");
        } else {
            $stub_template = file_get_contents($this->getStub('form.stub'));
            $pathToWrite = resource_path("views/livewire/$this->classNameSlug/$this->classNameSlug-form-page.blade.php");
        }

        $template = str_replace($search, $replace, $stub_template);
        $path = resource_path("views/livewire/$this->classNameSlug");
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);

        file_put_contents($pathToWrite, $template);
    }

    public function generateActionView(): void
    {
        $stubTemplate = [
            '{@primaryKey}',
            '{@className}',
            '{@classNameLower}',
            '{@classNameSlug}',
        ];
        $stubReplaceTemplate = [
            $this->primaryKey,
            $this->className,
            $this->classNameCamel,
            $this->classNameSlug,
        ];
        if ($this->isModal) {
            $stub_template = file_get_contents($this->getStub('action-modal.stub'));
        } else {
            $stub_template = file_get_contents($this->getStub('action.stub'));
        }
        $template = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);
        $path = resource_path("views/livewire/$this->classNameSlug");
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);
        $pathToWrite = resource_path("views/livewire/$this->classNameSlug/_$this->classNameSlug-action.blade.php");
        file_put_contents($pathToWrite, $template);
    }

    public function generateNavigationView(): void
    {
        $search = [
            '{@primaryKey}',
            '{@className}',
            '{@classNameLower}',
            '{@classNameSlug}',
            '{@classNameSpace}',
        ];
        $replace = [
            $this->primaryKey,
            $this->className,
            $this->classNameCamel,
            $this->classNameSlug,
            $this->classNameSpace,
        ];
        $subject = file_get_contents($this->getStub('nav.stub'));
        $template = str_replace($search, $replace, $subject);
        $path = resource_path("views/livewire/$this->classNameSlug");
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);
        $pathToWrite = resource_path("views/livewire/$this->classNameSlug/_$this->classNameSlug-nav.blade.php");
        file_put_contents($pathToWrite, $template);
    }

    public function generateFactory(): void
    {
        $definitions = View::make('wirecrud::string-builder._helper_props', [
            'pk' => $this->primaryKey,
            'fields' => $this->fields,
        ])->render();

        $stubTemplate = [
            '{@className}',
            '{@definitions}',
        ];
        $stubReplaceTemplate = [
            $this->className,
            $definitions,
        ];

        $stub_template = file_get_contents($this->getStub('factory.stub'));

        $template = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);
        file_put_contents(database_path("factories/{$this->className}Factory.php"), $template);
    }

    public function generateSeeder(): void
    {
        $stubTemplate = [
            '{@className}',
        ];
        $stubReplaceTemplate = [
            $this->className,
        ];

        $stub_template = file_get_contents($this->getStub('seeder.stub'));

        $template = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);
        file_put_contents(database_path("seeders/{$this->className}Seeder.php"), $template);
    }

    public function generateApi(): void
    {
        $validate = View::make('wirecrud::string-builder._helper_validate_generator_api', [
            'field_validate' => $this->fields->where('key_type', '<>', 'primary'),
            'classNameLower' => $this->classNameCamel,
        ])->render();

        $stubTemplate = [
            '{@className}',
            '{@classNameLower}',
            '{@classNameSlug}',
            '{@validate}',
        ];
        $stubReplaceTemplate = [
            $this->className,
            $this->classNameCamel,
            $this->classNameSlug,
            $validate,
        ];

        $stub_template = file_get_contents($this->getStub('api-controller.stub'));

        $template = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);
        file_put_contents(app_path("Http/Controllers/Api/V1/{$this->className}ApiController.php"), $template);
    }

    public function generateSortable(): void
    {

        $stubTemplate = [
            '{@className}',
            '{@classNameLower}',
            '{@classNameSlug}',
            '{@primaryKey}',
        ];
        $stubReplaceTemplate = [
            $this->className,
            $this->classNameCamel,
            $this->classNameSlug,
            $this->primaryKey,
        ];

        $path = app_path("Livewire/$this->className");
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);
        $stub_template = file_get_contents($this->getStub('sortable.stub'));
        $template = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);
        file_put_contents(app_path("Livewire/$this->className/{$this->className}Sortable.php"), $template);

        $path = resource_path("views/livewire/$this->classNameSlug");
        File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);
        $stub_template = file_get_contents($this->getStub('sortable-view.stub'));
        $template = str_replace($stubTemplate, $stubReplaceTemplate, $stub_template);
        $pathToFile = "views/livewire/$this->classNameSlug/$this->classNameSlug-sortable.blade.php";
        file_put_contents(filename: resource_path($pathToFile), data: $template);
    }
}
