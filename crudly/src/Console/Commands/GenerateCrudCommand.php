<?php

namespace Shahrakii\Crudly\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Shahrakii\Crudly\Crudly;

class GenerateCrudCommand extends Command
{
    protected $signature = 'crudly:generate 
        {model : The model name (e.g., Post)} 
        {--table= : Specify table name (optional)}
        {--force : Overwrite existing files}
        {--routes : Generate routes automatically}';

    protected $description = 'Generate complete CRUD operations for a model';

    protected $files;
    protected $crudly;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
        $this->crudly = new Crudly(app());
    }

    public function handle()
    {
        $model = $this->argument('model');
        $table = $this->option('table') ?? Str::snake(Str::pluralStudly($model));
        $force = $this->option('force');

        // Validate table exists
        if (!$this->crudly->tableExists($table)) {
            $this->error("Table '{$table}' does not exist!");
            return 1;
        }

        $this->info("🚀 Generating CRUD for {$model}...\n");

        // Generate model
        if ($this->generateModel($model, $table, $force)) {
            $this->info("✅ Generated Model: {$model}");
        }

        // Generate controller
        if ($this->generateController($model, $table, $force)) {
            $this->info("✅ Generated Controller: {$model}Controller");
        }

        // Generate views
        if ($this->generateViews($model, $table, $force)) {
            $this->info("✅ Generated Views");
        }

        // Generate migration (optional)
        if ($this->confirm('Generate migration file?')) {
            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
            ]);
        }

        // Generate routes (optional)
        if ($this->option('routes') || $this->confirm('Add routes to routes/web.php?')) {
            if ($this->generateRoutes($model)) {
                $this->info("✅ Routes added");
            }
        }

        $this->info("\n✨ CRUD generation complete!");
        $this->info("Run: php artisan serve");

        return 0;
    }

    /**
     * Generate Model file
     */
    protected function generateModel(string $model, string $table, bool $force): bool
    {
        $path = app_path("Models/{$model}.php");

        if ($this->files->exists($path) && !$force) {
            $this->warn("Model {$model} already exists. Use --force to overwrite.");
            return false;
        }

        $stub = $this->getModelStub($model, $table);
        $this->files->put($path, $stub);

        return true;
    }

    /**
     * Generate Controller file
     */
    protected function generateController(string $model, string $table, bool $force): bool
    {
        $path = app_path("Http/Controllers/{$model}Controller.php");

        if ($this->files->exists($path) && !$force) {
            $this->warn("Controller {$model}Controller already exists. Use --force to overwrite.");
            return false;
        }

        $columns = $this->crudly->getFilteredColumns($table);
        $rules = $this->crudly->getValidationRules($table);

        $stub = $this->getControllerStub($model, $table, $columns, $rules);
        $this->files->put($path, $stub);

        return true;
    }

    /**
     * Generate Blade views
     */
    protected function generateViews(string $model, string $table, bool $force): bool
    {
        $viewPath = resource_path("views/".Str::snake(Str::pluralStudly($model)));

        if ($this->files->exists($viewPath) && !$force) {
            $this->warn("Views already exist. Use --force to overwrite.");
            return false;
        }

        $this->files->makeDirectory($viewPath, 0755, true, true);

        $columns = $this->crudly->getFilteredColumns($table);
        $modelLower = Str::camel($model);
        $modelPlural = Str::snake(Str::pluralStudly($model));

        // Index view
        $this->files->put(
            "$viewPath/index.blade.php",
            $this->getIndexViewStub($model, $modelPlural, $modelLower, $columns)
        );

        // Create view
        $this->files->put(
            "$viewPath/create.blade.php",
            $this->getCreateViewStub($model, $modelPlural, $modelLower, $columns)
        );

        // Edit view
        $this->files->put(
            "$viewPath/edit.blade.php",
            $this->getEditViewStub($model, $modelPlural, $modelLower, $columns)
        );

        // Show view
        $this->files->put(
            "$viewPath/show.blade.php",
            $this->getShowViewStub($model, $modelPlural, $modelLower, $columns)
        );

        return true;
    }

    /**
     * Generate routes
     */
    protected function generateRoutes(string $model): bool
    {
        $routesPath = base_path('routes/web.php');
        $controller = "App\\Http\\Controllers\\{$model}Controller";
        $route = Str::snake(Str::pluralStudly($model));

        $routeContent = "Route::resource('{$route}', {$controller}::class);";

        $content = $this->files->get($routesPath);

        if (strpos($content, $routeContent) !== false) {
            $this->warn("Routes already exist.");
            return false;
        }

        // Append route before last closing
        $content = rtrim($content) . "\n\n// " . $model . " Routes\n" . $routeContent . "\n";
        $this->files->put($routesPath, $content);

        return true;
    }

    // ============ STUBS ============

    protected function getModelStub(string $model, string $table): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{MODEL}} extends Model
{
    use HasFactory;

    protected $table = '{{TABLE}}';

    protected $fillable = {{FILLABLE}};

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
PHP;
    }

    protected function getControllerStub(string $model, string $table, array $columns, array $rules): string
    {
        $modelLower = Str::camel($model);
        $modelPlural = Str::camel(Str::pluralStudly($model));
        $routeParam = Str::snake($model);

        $fillable = json_encode(array_map(fn($col) => $col['name'], $columns), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $rules = json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\\$model;
use Illuminate\Http\Request;

class {$model}Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \${$modelPlural} = $model::paginate(15);
        return view('{$modelPlural}.index', ['{$modelPlural}' => \${$modelPlural}]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('{$modelPlural}.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request \$request)
    {
        \$validated = \$request->validate({$rules});
        $model::create(\$validated);

        return redirect()->route('{$modelPlural}.index')
            ->with('success', '$model created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($model)
    {
        return view('{$modelPlural}.show', ['{$modelLower}' => \$$model]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($model)
    {
        return view('{$modelPlural}.edit', ['{$modelLower}' => \$$model]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request \$request, $model)
    {
        \$validated = \$request->validate({$rules});
        \$$model->update(\$validated);

        return redirect()->route('{$modelPlural}.index')
            ->with('success', '$model updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($model)
    {
        \$$model->delete();

        return redirect()->route('{$modelPlural}.index')
            ->with('success', '$model deleted successfully!');
    }
}
PHP;
    }

    protected function getIndexViewStub($model, $modelPlural, $modelLower, $columns): string
    {
        $columnHeaders = implode("\n", array_map(fn($col) => 
            "<th>" . Crudly::formatColumnLabel($col['name']) . "</th>", 
            $columns
        ));

        $columnData = implode("\n", array_map(fn($col) => 
            "<td>{{ \${$modelLower}->". $col['name'] . " }}</td>", 
            $columns
        ));

        return <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{$model}s</h1>
        <a href="{{ route('{$modelPlural}.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
            Create New
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    {$columnHeaders}
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse(\${$modelPlural} as \${$modelLower})
                    <tr class="hover:bg-gray-50">
                        {$columnData}
                        <td class="p-2">
                            <a href="{{ route('{$modelPlural}.show', \${$modelLower}) }}" class="text-blue-500 hover:underline">View</a>
                            <a href="{{ route('{$modelPlural}.edit', \${$modelLower}) }}" class="text-yellow-500 hover:underline ml-2">Edit</a>
                            <form action="{{ route('{$modelPlural}.destroy', \${$modelLower}) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Are you sure?')" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center p-4 text-gray-500">No records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ \${$modelPlural}->links() }}
    </div>
</div>
@endsection
BLADE;
    }

    protected function getCreateViewStub($model, $modelPlural, $modelLower, $columns): string
    {
        return <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-3xl font-bold mb-6">Create {$model}</h1>

    <form action="{{ route('{$modelPlural}.store') }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf

        @forelse(\$columns as \$column)
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">
                    {{ ucwords(str_replace('_', ' ', \$column['name'])) }}
                </label>
                <input 
                    type="{{ \$column['general_type'] === 'integer' ? 'number' : 'text' }}"
                    name="{{ \$column['name'] }}"
                    class="w-full border rounded px-3 py-2 @error(\$column['name']) border-red-500 @enderror"
                    required
                >
                @error(\$column['name'])
                    <span class="text-red-500 text-sm">{{ \$message }}</span>
                @enderror
            </div>
        @empty
            <p class="text-gray-500">No fields available</p>
        @endforelse

        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 w-full">
            Create
        </button>
    </form>
</div>
@endsection
BLADE;
    }

    protected function getEditViewStub($model, $modelPlural, $modelLower, $columns): string
    {
        return <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-3xl font-bold mb-6">Edit {$model}</h1>

    <form action="{{ route('{$modelPlural}.update', \${$modelLower}) }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')

        @forelse(\$columns as \$column)
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">
                    {{ ucwords(str_replace('_', ' ', \$column['name'])) }}
                </label>
                <input 
                    type="{{ \$column['general_type'] === 'integer' ? 'number' : 'text' }}"
                    name="{{ \$column['name'] }}"
                    value="{{ \${$modelLower}->{{ \$column['name'] }} }}"
                    class="w-full border rounded px-3 py-2 @error(\$column['name']) border-red-500 @enderror"
                    required
                >
                @error(\$column['name'])
                    <span class="text-red-500 text-sm">{{ \$message }}</span>
                @enderror
            </div>
        @empty
            <p class="text-gray-500">No fields available</p>
        @endforelse

        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 w-full">
            Update
        </button>
    </form>
</div>
@endsection
BLADE;
    }

    protected function getShowViewStub($model, $modelPlural, $modelLower, $columns): string
    {
        return <<<BLADE
@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-lg">
    <h1 class="text-3xl font-bold mb-6">{$model}</h1>

    <div class="bg-white p-6 rounded shadow">
        @forelse(\$columns as \$column)
            <div class="mb-4 pb-4 border-b">
                <h3 class="text-gray-700 font-bold">{{ ucwords(str_replace('_', ' ', \$column['name'])) }}</h3>
                <p class="text-gray-600">{{ \${$modelLower}->{{ \$column['name'] }} }}</p>
            </div>
        @empty
            <p class="text-gray-500">No fields available</p>
        @endforelse

        <div class="mt-6 flex gap-2">
            <a href="{{ route('{$modelPlural}.edit', \${$modelLower}) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">
                Edit
            </a>
            <form action="{{ route('{$modelPlural}.destroy', \${$modelLower}) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button onclick="return confirm('Are you sure?')" class="bg-red-500 text-white px-4 py-2 rounded">
                    Delete
                </button>
            </form>
            <a href="{{ route('{$modelPlural}.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">
                Back
            </a>
        </div>
    </div>
</div>
@endsection
BLADE;
    }
}
