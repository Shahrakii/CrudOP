<?php

namespace Shahrakii\Crudly\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateControllerCommand extends Command
{
    protected $signature = 'crudly:controller {name} {model} {--force}';
    protected $description = 'Generate a CRUD controller for a model';
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->argument('model');
        $force = $this->option('force');

        $path = app_path("Http/Controllers/{$name}.php");

        if ($this->files->exists($path) && !$force) {
            $this->error("Controller {$name} already exists!");
            return 1;
        }

        $stub = $this->getStub($name, $model);
        $this->files->put($path, $stub);

        $this->info("✅ Controller {$name} created successfully!");

        return 0;
    }

    protected function getStub(string $name, string $model): string
    {
        $modelLower = Str::camel($model);
        $modelPlural = Str::camel(Str::pluralStudly($model));

        return <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\\$model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class $name extends Controller
{
    protected \$table;
    protected \$excludeColumns = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected function getColumns()
    {
        \$all = Schema::getColumnListing(\$this->table);
        \$filtered = [];
        
        foreach (\$all as \$col) {
            if (!in_array(\$col, \$this->excludeColumns)) {
                \$filtered[] = [
                    'name' => \$col,
                    'general_type' => 'string'
                ];
            }
        }
        
        return \$filtered;
    }

    public function index()
    {
        \${$modelPlural} = $model::paginate(15);
        return view('index', ['{$modelPlural}' => \${$modelPlural}]);
    }

    public function create()
    {
        return view('create', ['columns' => \$this->getColumns()]);
    }

    public function store(Request \$request)
    {
        \$validated = \$request->validate([]);
        $model::create(\$validated);

        return redirect()->route('index')->with('success', 'Created successfully!');
    }

    public function show($model \${$modelLower})
    {
        return view('show', ['{$modelLower}' => \${$modelLower}, 'columns' => \$this->getColumns()]);
    }

    public function edit($model \${$modelLower})
    {
        return view('edit', ['{$modelLower}' => \${$modelLower}, 'columns' => \$this->getColumns()]);
    }

    public function update(Request \$request, $model \${$modelLower})
    {
        \$validated = \$request->validate([]);
        \${$modelLower}->update(\$validated);

        return redirect()->route('index')->with('success', 'Updated successfully!');
    }

    public function destroy($model \${$modelLower})
    {
        \${$modelLower}->delete();

        return redirect()->route('index')->with('success', 'Deleted successfully!');
    }
}
PHP;
    }
}