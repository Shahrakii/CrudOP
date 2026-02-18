<?php

namespace Shahrakii\Crudly\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateControllerCommand extends Command
{
    protected $signature = 'crudly:controller 
        {name : Controller name (e.g., PostController)} 
        {model : Model name (e.g., Post)} 
        {--force : Overwrite existing files}';

    protected $description = 'Generate a CRUD controller for a model';

    protected $files;
    protected $stubCache = [];

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $startTime = microtime(true);
        $name = $this->argument('name');
        $model = $this->argument('model');
        $force = $this->option('force');

        $path = app_path("Http/Controllers/{$name}.php");

        if ($this->files->exists($path) && !$force) {
            $this->error("❌ Controller {$name} already exists!");
            $this->info("💡 Use --force to overwrite");
            return 1;
        }

        $stub = $this->getStub($name, $model);
        $this->files->put($path, $stub);

        $duration = number_format(microtime(true) - $startTime, 3);
        $this->info("✅ Controller {$name} created successfully! ({$duration}s)");

        return 0;
    }

    protected function getStub(string $name, string $model): string
    {
        $cacheKey = "controller_{$model}";
        
        if (isset($this->stubCache[$cacheKey])) {
            return $this->stubCache[$cacheKey];
        }

        // Try to load from file first
        $stub = $this->loadStubFile('controller/controller.stub');
        
        if ($stub === null) {
            $stub = $this->getInlineStub();
        }

        $modelLower = Str::camel($model);
        $modelPlural = Str::camel(Str::pluralStudly($model));

        $stub = str_replace(
            ['{{ CONTROLLER_NAME }}', '{{ MODEL }}', '{{ MODEL_LOWER }}', '{{ MODEL_PLURAL }}'],
            [$name, $model, $modelLower, $modelPlural],
            $stub
        );

        $this->stubCache[$cacheKey] = $stub;
        return $stub;
    }

    protected function loadStubFile(string $path): ?string
    {
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

        return null;
    }

    protected function getInlineStub(): string
    {
        return '<?php

namespace App\Http\Controllers;

use App\Models\{{ MODEL }};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class {{ CONTROLLER_NAME }} extends Controller
{
    protected $table;
    protected $excludeColumns = [\'id\', \'created_at\', \'updated_at\', \'deleted_at\'];

    /**
     * Get filtered columns (excluding system columns)
     */
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        ${{ MODEL_PLURAL }} = {{ MODEL }}::paginate(15);
        return view(\'{{ MODEL_LOWER | pluralize }}.index\', [\'{{ MODEL_PLURAL }}\' => ${{ MODEL_PLURAL }}]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view(\'create\', [\'columns\' => $this->getColumns()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([]);
        {{ MODEL }}::create($validated);

        return redirect()->route(\'index\')->with(\'success\', \'{{ MODEL }} created successfully!\');
    }

    /**
     * Display the specified resource.
     */
    public function show({{ MODEL }} ${{ MODEL_LOWER }})
    {
        return view(\'show\', [\'{{ MODEL_LOWER }}\' => ${{ MODEL_LOWER }}, \'columns\' => $this->getColumns()]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit({{ MODEL }} ${{ MODEL_LOWER }})
    {
        return view(\'edit\', [\'{{ MODEL_LOWER }}\' => ${{ MODEL_LOWER }}, \'columns\' => $this->getColumns()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, {{ MODEL }} ${{ MODEL_LOWER }})
    {
        $validated = $request->validate([]);
        ${{ MODEL_LOWER }}->update($validated);

        return redirect()->route(\'index\')->with(\'success\', \'{{ MODEL }} updated successfully!\');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy({{ MODEL }} ${{ MODEL_LOWER }})
    {
        ${{ MODEL_LOWER }}->delete();

        return redirect()->route(\'index\')->with(\'success\', \'{{ MODEL }} deleted successfully!\');
    }
}';
    }
}
