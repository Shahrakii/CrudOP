<?php

namespace Shahrakii\Crudly\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateModelCommand extends Command
{
    protected $signature = 'crudly:model 
        {name : Model name (e.g., Post)} 
        {--force : Overwrite existing files}';

    protected $description = 'Generate a model with fillable properties';

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
        $force = $this->option('force');

        $path = app_path("Models/{$name}.php");

        if ($this->files->exists($path) && !$force) {
            $this->error("❌ Model {$name} already exists!");
            $this->info("💡 Use --force to overwrite");
            return 1;
        }

        $stub = $this->getStub($name);
        $this->files->put($path, $stub);

        $duration = number_format(microtime(true) - $startTime, 3);
        $this->info("✅ Model {$name} created successfully! ({$duration}s)");
        $this->info("📍 Location: app/Models/{$name}.php");

        return 0;
    }

    protected function getStub(string $name): string
    {
        $cacheKey = "model_{$name}";
        
        if (isset($this->stubCache[$cacheKey])) {
            return $this->stubCache[$cacheKey];
        }

        // Try to load from file first
        $stub = $this->loadStubFile('model/model.stub');
        
        if ($stub === null) {
            $stub = $this->getInlineStub();
        }

        $stub = str_replace(['{{ MODEL }}', '{{ NAME }}'], [$name, $name], $stub);

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

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{ NAME }} extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        //
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        \'created_at\' => \'datetime\',
        \'updated_at\' => \'datetime\',
    ];
}';
    }
}
