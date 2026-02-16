# 🚀 Crudly - Intelligent CRUD Generator for Laravel 12

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](LICENSE)
[![Latest Stable Version](https://img.shields.io/badge/version-1.0.0-blue.svg?style=flat-square)](https://github.com/Shahrakii/Crudly/releases)

Crudly is a powerful, intelligent CRUD generator for Laravel 12 that automatically generates complete CRUD operations (Controllers, Models, Migrations, Views) from your database schema in seconds.

## ✨ Features

- ✅ **Automatic Code Generation** - Generate Controllers, Models, and Views in one command
- ✅ **Smart Schema Detection** - Automatically detects column types and relationships
- ✅ **Validation Generation** - Creates validation rules based on database schema
- ✅ **Relationship Handling** - Supports foreign keys and relationships
- ✅ **Multiple CSS Frameworks** - Tailwind CSS and Bootstrap support
- ✅ **Customizable** - Easily extendable and configurable
- ✅ **Artisan Commands** - Simple CLI commands for generation
- ✅ **Blade Templates** - Professional, responsive Blade templates
- ✅ **Pagination Support** - Built-in pagination for list views
- ✅ **Enum Support** - Handles ENUM column types automatically
- ✅ **Production Ready** - Follows Laravel best practices

## 📋 Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Commands](#commands)
- [Configuration](#configuration)
- [Usage Examples](#usage-examples)
- [Advanced Usage](#advanced-usage)
- [Customization](#customization)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## 🔧 Requirements

- PHP 8.2+
- Laravel 12.0+
- Composer

## 📦 Installation

### Step 1: Install via Composer

```bash
composer require shahrakii/crudly
```

### Step 2: Publish Configuration

```bash
php artisan vendor:publish --provider="Shahrakii\Crudly\CrudlyServiceProvider" --tag="crudly-config"
```

### Step 3: Publish Views (Optional)

```bash
php artisan vendor:publish --provider="Shahrakii\Crudly\CrudlyServiceProvider" --tag="crudly-views"
```

That's it! You're ready to use Crudly.

## 🚀 Quick Start

### Generate Complete CRUD for a Model

```bash
php artisan crudly:generate Post
```

This will:
- ✅ Create `app/Models/Post.php`
- ✅ Create `app/Http/Controllers/PostController.php`
- ✅ Create Blade views (index, create, edit, show)
- ✅ Generate proper validation rules

### Access Your CRUD

Add routes to `routes/web.php`:

```php
Route::resource('posts', PostController::class);
```

Then visit:
- List: `http://localhost:8000/posts`
- Create: `http://localhost:8000/posts/create`
- Edit: `http://localhost:8000/posts/1/edit`
- View: `http://localhost:8000/posts/1`

## 📌 Commands

### Generate CRUD

```bash
php artisan crudly:generate {model} {--table=} {--force} {--routes}
```

**Arguments:**
- `{model}` - The model name (e.g., `Post`, `Product`, `User`)

**Options:**
- `--table=` - Specify the table name (default: plural of model in snake_case)
- `--force` - Overwrite existing files
- `--routes` - Add routes to `routes/web.php` automatically

**Example:**

```bash
# Generate CRUD for Post model
php artisan crudly:generate Post

# Generate for custom table
php artisan crudly:generate Post --table=blog_posts

# Overwrite existing files
php artisan crudly:generate Post --force

# Add routes automatically
php artisan crudly:generate Post --routes
```

## ⚙️ Configuration

Edit `config/crudly.php`:

```php
return [
    // Route prefix
    'route_prefix' => 'admin',

    // Middleware
    'middleware' => ['web', 'auth'],

    // Columns to exclude from all CRUD operations
    'global_filters' => [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    // Table-specific column filters
    'table_filters' => [
        'users' => ['password', 'remember_token'],
    ],

    // Tables to exclude
    'exclude_tables' => [
        'migrations',
        'failed_jobs',
    ],

    // Pagination per page
    'pagination' => 15,

    // CSS Framework
    'css_framework' => 'tailwind', // or 'bootstrap'

    // Auto-generate routes
    'auto_routes' => false,

    // Generate factories
    'generate_factories' => true,
];
```

## 💡 Usage Examples

### Example 1: Blog Post CRUD

```bash
# Create migration first
php artisan make:migration create_posts_table

# In migration:
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->string('slug')->unique();
    $table->unsignedBigInteger('author_id');
    $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
    $table->timestamps();
    $table->foreign('author_id')->references('id')->on('users');
});

# Run migration
php artisan migrate

# Generate CRUD
php artisan crudly:generate Post --routes
```

### Example 2: Product Management

```bash
# Create migration
php artisan make:migration create_products_table

# In migration:
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description');
    $table->decimal('price', 8, 2);
    $table->integer('stock');
    $table->string('sku')->unique();
    $table->timestamps();
});

# Run migration
php artisan migrate

# Generate CRUD with force flag
php artisan crudly:generate Product --force --routes
```

### Example 3: With Relationships

```bash
# Create tables
php artisan make:migration create_categories_table
php artisan make:migration create_items_table

# Run migrations
php artisan migrate

# Generate CRUD for both
php artisan crudly:generate Category
php artisan crudly:generate Item
```

## 🔮 Advanced Usage

### Customize Generated Controller

After generation, edit `app/Http/Controllers/PostController.php`:

```php
public function index()
{
    // Add custom logic
    $posts = Post::where('status', 'published')
        ->orderBy('created_at', 'desc')
        ->paginate(15);

    return view('posts.index', ['posts' => $posts]);
}
```

### Customize Generated Views

Edit views in `resources/views/posts/`:

```blade
<!-- Custom validation -->
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### Add Custom Validation Rules

Edit generated validation rules:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'slug' => 'required|string|unique:posts,slug',
        'content' => 'required|string|min:10',
        'status' => 'required|in:draft,published,archived',
        'author_id' => 'required|exists:users,id',
    ]);

    Post::create($validated);

    return redirect()->route('posts.index')
        ->with('success', 'Post created successfully!');
}
```

## 🎨 Customization

### Change CSS Framework

Update `.env`:

```env
CRUDLY_CSS_FRAMEWORK=bootstrap
```

Or in `config/crudly.php`:

```php
'css_framework' => 'bootstrap',
```

### Exclude Columns

In `config/crudly.php`:

```php
'table_filters' => [
    'users' => ['password', 'remember_token', 'api_token'],
    'posts' => ['admin_notes'],
],
```

### Use Crudly Facade

```php
use Shahrakii\Crudly\Facades\Crudly;

// Get table columns
$columns = Crudly::getTableColumns('posts');

// Get validation rules
$rules = Crudly::getValidationRules('posts');

// Get all tables
$tables = Crudly::getAllTables();
```

## 🧪 Testing

Run Crudly tests:

```bash
composer test
```

Test a specific feature:

```bash
php artisan crudly:generate TestModel --force
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

Crudly is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Support

If you have any questions or issues, please open an issue on [GitHub](https://github.com/Shahrakii/Crudly/issues).

## 🎯 Roadmap

- [ ] API resource generation
- [ ] GraphQL support
- [ ] Advanced filtering
- [ ] Bulk operations
- [ ] Audit logging
- [ ] Workflow engine
- [ ] Multi-language support

---

**Made with ❤️ for Laravel developers**
