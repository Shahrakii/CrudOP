<?php

namespace Shahrakii\Crudly\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateModelCommand extends Command
{
    protected $signature = 'crudly:model {name} {--force}';
    protected $description = 'Generate a model with fillable properties';
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');
        $force = $this->option('force');

        $path = app_path("Models/{$name}.php");

        if ($this->files->exists($path) && !$force) {
            $this->error("Model {$name} already exists!");
            return 1;
        }

        $stub = $this->getStub($name);
        $this->files->put($path, $stub);

        $this->info("✅ Model {$name} created successfully!");

        return 0;
    }

    protected function getStub(string $name): string
    {
        return <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{ NAME }} extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
PHP;
    }
}