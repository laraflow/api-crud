<?php

namespace Laraflow\ApiCrud\Commands;

use Illuminate\Support\Str;
use Laraflow\ApiCrud\Abstracts\GeneratorCommand;
use Laraflow\ApiCrud\Exceptions\GeneratorException;
use Laraflow\ApiCrud\Support\Stub;
use Laraflow\ApiCrud\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The stub file type
     *
     * @var string
     */
    protected $type = 'model';

    /**
     * The name of argument name.
     *
     * @var string
     */
    protected $argumentName = 'name';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laraflow:make-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for the specified package.';

    /**
     * @return mixed|string
     */
    private function getModelName()
    {
        return Str::studly($this->argument('name'));
    }

    /**
     * Create a seeder file for the model.
     * @experimental
     * @return void
     */
    protected function handleOptionalSeedOption()
    {
        if ($this->option('seed') === true) {
            $seedName = "{$this->getModelName()}Seeder";

            $this->call('laraflow:make-seed', array_filter([
                'name' => $seedName,
                'module' => $this->argument('module'),
            ]));
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of model will be created.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['fillable', null, InputOption::VALUE_OPTIONAL, 'The fillable attributes.', null],
            ['migration', 'm', InputOption::VALUE_NONE, 'Flag to create associated migrations', null],
            ['controller', 'c', InputOption::VALUE_NONE, 'Flag to create associated controllers', null],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder for the model', null],
            ['request', 'r', InputOption::VALUE_NONE, 'Create a new request for the model', null],
        ];
    }

    /**
     * @return mixed
     *
     * @throws GeneratorException
     */
    protected function getTemplateContents(): string
    {
        return (new Stub('/model.stub', [
            'NAME' => $this->getModelName(),
            'ROUTE_NAME' => Str::plural(Str::lower(Str::kebab($this->getModelName()))),
            'JSON_NAME' => Str::lower(Str::snake($this->getModelName())).'_data',
            'TABLE' => $this->getTableName(),
            'FILLABLE' => $this->getFillable(),
            'NAMESPACE' => $this->getClassNamespace($this->getModuleName()),
            'CLASS' => $this->getClass(),
            'LOWER_CLASS' => Str::lower($this->getClass()),
            'LOWER_NAME' => Str::lower($this->getModuleName()),
            'MODULE' => $this->getModuleName(),
            'STUDLY_NAME' => $this->getModuleName(),
            'MODULE_NAMESPACE' => config('fintech.generators.namespace'),
        ]))->render();
    }

    /**
     * @experimental
     * @return string
     */
    private function getFillable()
    {
        $fillable = $this->option('fillable');

        if (! is_null($fillable)) {
            $arrays = explode(',', $fillable);

            return json_encode($arrays);
        }

        return '[]';
    }

    /**
     * @return string
     */
    protected function getFileName(): string
    {
        return Str::studly($this->argument('name')).'.php';
    }
}
