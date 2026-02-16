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

        return <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\\$model;
use Illuminate\Http\Request;

class $name extends Controller
{
    public function index()
    {
        \${$modelLower}s = $model::paginate(15);
        return view('index', ['{$modelLower}s' => \${$modelLower}s]);
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request \$request)
    {
        \$validated = \$request->validate([]);
        $model::create(\$validated);
        return redirect()->route('index')->with('success', 'Created successfully!');
    }

    public function show($model)
    {
        return view('show', ['{$modelLower}' => \$$model]);
    }

    public function edit($model)
    {
        return view('edit', ['{$modelLower}' => \$$model]);
    }

    public function update(Request \$request, $model)
    {
        \$validated = \$request->validate([]);
        \$$model->update(\$validated);
        return redirect()->route('index')->with('success', 'Updated successfully!');
    }

    public function destroy($model)
    {
        \$$model->delete();
        return redirect()->route('index')->with('success', 'Deleted successfully!');
    }
}
PHP;
    }
}
