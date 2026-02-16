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

        if (!$this->crudly->tableExists($table)) {
            $this->error("Table '{$table}' does not exist!");
            return 1;
        }

        $this->info("🚀 Generating CRUD for {$model}...\n");

        if ($this->generateModel($model, $table, $force)) {
            $this->info("✅ Generated Model: {$model}");
        }

        if ($this->generateController($model, $table, $force)) {
            $this->info("✅ Generated Controller: {$model}Controller");
        }

        if ($this->generateViews($model, $table, $force)) {
            $this->info("✅ Generated Views");
        }

        if ($this->confirm('Generate migration file?')) {
            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
            ]);
        }

        if ($this->option('routes') || $this->confirm('Add routes to routes/web.php?')) {
            if ($this->generateRoutes($model)) {
                $this->info("✅ Routes added");
            }
        }

        $this->info("\n✨ CRUD generation complete!");
        $this->info("Run: php artisan serve");
        $this->info("📝 Visit: http://localhost:8000/" . Str::snake(Str::pluralStudly($model)));

        return 0;
    }

    protected function generateModel(string $model, string $table, bool $force): bool
    {
        $path = app_path("Models/{$model}.php");

        if ($this->files->exists($path) && !$force) {
            $this->warn("Model {$model} already exists. Use --force to overwrite.");
            return false;
        }

        $columns = $this->crudly->getFilteredColumns($table);
        $fillable = var_export(array_map(fn($col) => $col['name'], $columns), true);
        
        $stub = $this->loadStub('model/model.stub');
        $stub = str_replace(['{{ MODEL }}', '{{ TABLE }}', '{{ FILLABLE }}'], 
            [$model, $table, $fillable], $stub);
        
        $this->files->put($path, $stub);

        return true;
    }

    protected function generateController(string $model, string $table, bool $force): bool
    {
        $path = app_path("Http/Controllers/{$model}Controller.php");

        if ($this->files->exists($path) && !$force) {
            $this->warn("Controller {$model}Controller already exists. Use --force to overwrite.");
            return false;
        }

        $columns = $this->crudly->getFilteredColumns($table);
        $rules = $this->crudly->getValidationRules($table);
        $rulesExport = var_export($rules, true);

        $modelPlural = Str::camel(Str::pluralStudly($model));
        $modelPluralSnake = Str::snake(Str::pluralStudly($model));
        $modelLower = Str::camel($model);

        $stub = $this->loadStub('controller/controller.stub');
        $stub = str_replace(
            ['{{ MODEL }}', '{{ TABLE }}', '{{ RULES }}', '{{ MODEL_PLURAL_CAMEL }}', '{{ MODEL_PLURAL_SNAKE }}', '{{ MODEL_LOWER }}'],
            [$model, $table, $rulesExport, $modelPlural, $modelPluralSnake, $modelLower],
            $stub
        );

        $this->files->put($path, $stub);

        return true;
    }

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
        $modelPluralCamel = Str::camel(Str::pluralStudly($model));

        // Index view
        $columnHeaders = implode("\n                        ", array_map(fn($col) => 
            "<th class=\"px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider\">" . 
            Str::of($col['name'])->replace('_', ' ')->title() . "</th>", 
            $columns
        ));

        $columnData = implode("\n                            ", array_map(fn($col) => 
            "<td class=\"px-6 py-4 text-sm text-gray-300\">{{ \${$modelLower}->{$col['name']} }}</td>", 
            $columns
        ));

        $stub = $this->loadStub('views/index.stub');
        $stub = str_replace(
            ['{{ MODEL }}', '{{ MODEL_PLURAL }}', '{{ MODEL_LOWER_PLURAL }}', '{{ COLUMN_HEADERS }}', '{{ COLUMN_DATA }}', '{{ MODEL_PLURAL_SNAKE }}', '{{ MODEL_PLURAL_CAMEL }}', '{{ MODEL_LOWER }}'],
            [$model, Str::plural($model), Str::plural(Str::lower($model)), $columnHeaders, $columnData, $modelPlural, $modelPluralCamel, $modelLower],
            $stub
        );

        $this->files->put("$viewPath/index.blade.php", $stub);

        // Create/Edit form fields
        $formFields = implode("\n\n        ", array_map(fn($col) => 
            $this->generateFormField($col['name'], $modelLower), 
            $columns
        ));

        // Create view
        $createStub = $this->loadStub('views/create.stub');
        $createStub = str_replace(
            ['{{ MODEL }}', '{{ MODEL_PLURAL_SNAKE }}', '{{ MODEL_LOWER }}', '{{ FORM_FIELDS }}'],
            [$model, $modelPlural, $modelLower, $formFields],
            $createStub
        );
        $this->files->put("$viewPath/create.blade.php", $createStub);

        // Edit view
        $editStub = $this->loadStub('views/edit.stub');
        $editStub = str_replace(
            ['{{ MODEL }}', '{{ MODEL_PLURAL_SNAKE }}', '{{ MODEL_LOWER }}', '{{ FORM_FIELDS }}'],
            [$model, $modelPlural, $modelLower, $formFields],
            $editStub
        );
        $this->files->put("$viewPath/edit.blade.php", $editStub);

        // Show view - display fields
        $displayFields = implode("\n\n        ", array_map(fn($col) => 
            $this->generateDisplayField($col['name'], $modelLower), 
            $columns
        ));

        $showStub = $this->loadStub('views/show.stub');
        $showStub = str_replace(
            ['{{ MODEL }}', '{{ MODEL_PLURAL_SNAKE }}', '{{ MODEL_LOWER }}', '{{ DISPLAY_FIELDS }}'],
            [$model, $modelPlural, $modelLower, $displayFields],
            $showStub
        );
        $this->files->put("$viewPath/show.blade.php", $showStub);

        return true;
    }

    protected function generateFormField(string $fieldName, string $modelLower): string
    {
        $stub = $this->loadStub('views/form-field.stub');
        $label = Str::of($fieldName)->replace('_', ' ')->title();
        
        return str_replace(
            ['{{ FIELD_NAME }}', '{{ FIELD_LABEL }}', '{{ FIELD_LABEL_LOWER }}', '{{ FIELD_TYPE }}', '{{ MODEL_LOWER }}', '{{ FIELD_REQUIRED }}'],
            [$fieldName, $label, Str::lower($label), 'text', $modelLower, 'required'],
            $stub
        );
    }

    protected function generateDisplayField(string $fieldName, string $modelLower): string
    {
        $stub = $this->loadStub('views/display-field.stub');
        $label = Str::of($fieldName)->replace('_', ' ')->title();
        
        return str_replace(
            ['{{ FIELD_NAME }}', '{{ FIELD_LABEL }}', '{{ MODEL_LOWER }}'],
            [$fieldName, $label, $modelLower],
            $stub
        );
    }

    protected function loadStub(string $path): string
    {
        // Try multiple stub paths
        $possiblePaths = [
            __DIR__ . '/../../../resources/stubs/' . $path,
            base_path('vendor/shahrakii/crudly/resources/stubs/' . $path),
            dirname(__FILE__, 3) . '/resources/stubs/' . $path,
        ];
        
        foreach ($possiblePaths as $stubPath) {
            if ($this->files->exists($stubPath)) {
                return $this->files->get($stubPath);
            }
        }
        
        return $this->getInlineStub($path);
    }

    protected function getInlineStub(string $stubName): string
    {
        $stubs = [
            'model/model.stub' => '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{ MODEL }} extends Model
{
    use HasFactory;

    protected $table = \'{{ TABLE }}\';

    protected $fillable = {{ FILLABLE }};

    protected $casts = [
        \'created_at\' => \'datetime\',
        \'updated_at\' => \'datetime\',
    ];
}',
            'controller/controller.stub' => '<?php

namespace App\Http\Controllers;

use App\Models\{{ MODEL }};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class {{ MODEL }}Controller extends Controller
{
    protected $table = \'{{ TABLE }}\';
    protected $excludeColumns = [\'id\', \'created_at\', \'updated_at\', \'deleted_at\'];

    protected function getColumns()
    {
        $all = Schema::getColumnListing($this->table);
        $filtered = [];
        
        foreach ($all as $col) {
            if (!in_array($col, $this->excludeColumns)) {
                $filtered[] = [
                    \'name\' => $col,
                    \'general_type\' => \'string\'
                ];
            }
        }
        
        return $filtered;
    }

    public function index()
    {
        ${{ MODEL_PLURAL_CAMEL }} = {{ MODEL }}::paginate(15);
        return view(\'{{ MODEL_PLURAL_SNAKE }}.index\', [\'{{ MODEL_PLURAL_CAMEL }}\' => ${{ MODEL_PLURAL_CAMEL }}]);
    }

    public function create()
    {
        return view(\'{{ MODEL_PLURAL_SNAKE }}.create\', [\'columns\' => $this->getColumns()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate({{ RULES }});
        {{ MODEL }}::create($validated);

        return redirect()->route(\'{{ MODEL_PLURAL_SNAKE }}.index\')
            ->with(\'success\', \'{{ MODEL }} created successfully!\');
    }

    public function show({{ MODEL }} ${{ MODEL_LOWER }})
    {
        return view(\'{{ MODEL_PLURAL_SNAKE }}.show\', [\'columns\' => $this->getColumns(), \'{{ MODEL_LOWER }}\' => ${{ MODEL_LOWER }}]);
    }

    public function edit({{ MODEL }} ${{ MODEL_LOWER }})
    {
        return view(\'{{ MODEL_PLURAL_SNAKE }}.edit\', [\'columns\' => $this->getColumns(), \'{{ MODEL_LOWER }}\' => ${{ MODEL_LOWER }}]);
    }

    public function update(Request $request, {{ MODEL }} ${{ MODEL_LOWER }})
    {
        $validated = $request->validate({{ RULES }});
        ${{ MODEL_LOWER }}->update($validated);

        return redirect()->route(\'{{ MODEL_PLURAL_SNAKE }}.index\')
            ->with(\'success\', \'{{ MODEL }} updated successfully!\');
    }

    public function destroy({{ MODEL }} ${{ MODEL_LOWER }})
    {
        ${{ MODEL_LOWER }}->delete();

        return redirect()->route(\'{{ MODEL_PLURAL_SNAKE }}.index\')
            ->with(\'success\', \'{{ MODEL }} deleted successfully!\');
    }
}',
            'views/index.stub' => '@extends(\'layouts.app\')

@section(\'page-title\', \'{{ MODEL_PLURAL }}\')
@section(\'page-subtitle\', \'Manage all {{ MODEL_LOWER_PLURAL }} in your system\')

@section(\'content\')
<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">{{ MODEL_PLURAL }}</h1>
            <p class="text-gray-400">Total: <span class="font-semibold text-blue-400">{{ ${{ MODEL_PLURAL_CAMEL }}->total() }}</span></p>
        </div>
        <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.create\') }}" class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
            <i class="fas fa-plus"></i>
            Create New
        </a>
    </div>

    <div class="bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-900/50 border-b border-gray-700">
                    <tr>
                        {{ COLUMN_HEADERS }}
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse(${{ MODEL_PLURAL_CAMEL }} as ${{ MODEL_LOWER }})
                        <tr class="hover:bg-gray-700/50 transition-colors">
                            {{ COLUMN_DATA }}
                            <td class="px-6 py-4 text-sm space-x-3 flex items-center">
                                <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.show\', ${{ MODEL_LOWER }}) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-all text-xs font-medium">
                                    <i class="fas fa-eye text-xs"></i>
                                    View
                                </a>
                                <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.edit\', ${{ MODEL_LOWER }}) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 transition-all text-xs font-medium">
                                    <i class="fas fa-edit text-xs"></i>
                                    Edit
                                </a>
                                <form action="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.destroy\', ${{ MODEL_LOWER }}) }}" method="POST" class="inline">
                                    @csrf
                                    @method(\'DELETE\')
                                    <button onclick="return confirm(\'Are you sure?\')" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-all text-xs font-medium">
                                        <i class="fas fa-trash text-xs"></i>
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-inbox text-4xl text-gray-600"></i>
                                    <p class="text-gray-400 font-medium">No {{ MODEL_LOWER_PLURAL }} found</p>
                                    <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.create\') }}" class="text-blue-400 hover:text-blue-300 text-sm">Create one now →</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(${{ MODEL_PLURAL_CAMEL }}->hasPages())
        <div class="mt-8 flex justify-center">
            {{ ${{ MODEL_PLURAL_CAMEL }}->links() }}
        </div>
    @endif
</div>
@endsection',
            'views/create.stub' => '@extends(\'layouts.app\')

@section(\'page-title\', \'Create {{ MODEL }}\')
@section(\'page-subtitle\', \'Add a new {{ MODEL_LOWER }} to your system\')

@section(\'content\')
<div class="p-6 max-w-2xl">
    <form action="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.store\') }}" method="POST" class="space-y-6">
        @csrf

        {{ FORM_FIELDS }}

        <div class="flex gap-3 pt-6 border-t border-gray-700">
            <button type="submit" class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-6 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                <i class="fas fa-check"></i>
                Create
            </button>
            <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.index\') }}" class="flex-1 flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-600 text-gray-100 px-6 py-3 rounded-lg font-medium transition-all">
                <i class="fas fa-times"></i>
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection',
            'views/edit.stub' => '@extends(\'layouts.app\')

@section(\'page-title\', \'Edit {{ MODEL }}\')
@section(\'page-subtitle\', \'Update {{ MODEL_LOWER }} information\')

@section(\'content\')
<div class="p-6 max-w-2xl">
    <form action="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.update\', ${{ MODEL_LOWER }}) }}" method="POST" class="space-y-6">
        @csrf
        @method(\'PUT\')

        {{ FORM_FIELDS }}

        <div class="flex gap-3 pt-6 border-t border-gray-700">
            <button type="submit" class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                <i class="fas fa-save"></i>
                Update
            </button>
            <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.index\') }}" class="flex-1 flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-600 text-gray-100 px-6 py-3 rounded-lg font-medium transition-all">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </form>
</div>
@endsection',
            'views/show.stub' => '@extends(\'layouts.app\')

@section(\'page-title\', \'{{ MODEL }} Details\')
@section(\'page-subtitle\', \'View complete information\')

@section(\'content\')
<div class="p-6 max-w-2xl">
    <div class="space-y-6">
        {{ DISPLAY_FIELDS }}

        <div class="flex gap-3 pt-6 border-t border-gray-700">
            <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.edit\', ${{ MODEL_LOWER }}) }}" class="flex-1 flex items-center justify-center gap-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white px-6 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <form action="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.destroy\', ${{ MODEL_LOWER }}) }}" method="POST" class="flex-1">
                @csrf
                @method(\'DELETE\')
                <button onclick="return confirm(\'Are you sure?\')" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-trash"></i>
                    Delete
                </button>
            </form>
            <a href="{{ route(\'{{ MODEL_PLURAL_SNAKE }}.index\') }}" class="flex-1 flex items-center justify-center gap-2 bg-gray-700 hover:bg-gray-600 text-gray-100 px-6 py-3 rounded-lg font-medium transition-all">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>
</div>
@endsection',
            'views/form-field.stub' => '<div class="space-y-2">
    <label for="{{ FIELD_NAME }}" class="block text-sm font-semibold text-gray-300">
        {{ FIELD_LABEL }} <span class="text-red-400">*</span>
    </label>
    <input 
        type="{{ FIELD_TYPE }}"
        id="{{ FIELD_NAME }}"
        name="{{ FIELD_NAME }}"
        value="{{ old(\'{{ FIELD_NAME }}\', ${{ MODEL_LOWER }}->{{ FIELD_NAME }} ?? \'\') }}"
        class="w-full px-4 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition @error(\'{{ FIELD_NAME }}\') border-red-500 @enderror"
        placeholder="Enter {{ FIELD_LABEL_LOWER }}"
        {{ FIELD_REQUIRED }}
    >
    @error(\'{{ FIELD_NAME }}\')
        <p class="text-red-400 text-xs flex items-center gap-1">
            <i class="fas fa-exclamation-circle"></i>
            {{ $message }}
        </p>
    @enderror
</div>',
            'views/display-field.stub' => '<div class="space-y-1 p-4 bg-gray-700/50 rounded-lg">
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ FIELD_LABEL }}</p>
    <p class="text-lg text-gray-100 font-medium">{{ ${{ MODEL_LOWER }}->{{ FIELD_NAME }} ?? \'N/A\' }}</p>
</div>',
        ];
        
        if (!isset($stubs[$stubName])) {
            throw new \RuntimeException("Stub not found: {$stubName}");
        }
        
        return $stubs[$stubName];
    }

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

        $content = rtrim($content) . "\n\n// " . $model . " Routes\n" . $routeContent . "\n";
        $this->files->put($routesPath, $content);

        return true;
    }
}