# 📖 Crudly Usage Guide

## Quick Start (5 Minutes)

### 1. Create a Database Table

Option A: Using Migrations
```bash
php artisan make:migration create_posts_table
```

Edit migration file:
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->string('slug')->unique();
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamps();
});
```

Run migration:
```bash
php artisan migrate
```

### 2. Generate CRUD

```bash
php artisan crudly:generate Post --routes
```

That's it! Your complete CRUD is ready.

### 3. Visit Your CRUD

- **List:** `http://localhost:8000/posts`
- **Create:** `http://localhost:8000/posts/create`
- **Edit:** `http://localhost:8000/posts/1/edit`
- **View:** `http://localhost:8000/posts/1`
- **Delete:** Click delete button

---

## 📚 Full Examples

### Example 1: Blog Application

```bash
# Create migrations
php artisan make:migration create_categories_table
php artisan make:migration create_posts_table

# Edit migrations
# categories: id, name, slug, timestamps
# posts: id, title, content, slug, category_id (FK), status, timestamps

# Run migrations
php artisan migrate

# Generate CRUD
php artisan crudly:generate Category --routes
php artisan crudly:generate Post --routes
```

### Example 2: E-Commerce Store

```bash
# Create migrations
php artisan make:migration create_products_table
php artisan make:migration create_categories_table
php artisan make:migration create_orders_table
php artisan make:migration create_order_items_table

# Run migrations
php artisan migrate

# Generate CRUD
php artisan crudly:generate Product --routes
php artisan crudly:generate Category --routes
php artisan crudly:generate Order --routes
php artisan crudly:generate OrderItem --routes
```

### Example 3: User Management

```bash
# Migration already exists, just generate
php artisan crudly:generate User --table=users --routes
```

---

## 🔧 Command Reference

### Generate Complete CRUD

```bash
php artisan crudly:generate {ModelName}
```

**Options:**

| Option | Description | Example |
|--------|-------------|---------|
| `--table=` | Custom table name | `--table=blog_posts` |
| `--force` | Overwrite existing files | `--force` |
| `--routes` | Add routes automatically | `--routes` |

**Examples:**

```bash
# Basic generation
php artisan crudly:generate Post

# Custom table
php artisan crudly:generate BlogPost --table=blog_posts

# Overwrite existing
php artisan crudly:generate Post --force

# Generate and add routes
php artisan crudly:generate Post --routes

# All options
php artisan crudly:generate Post --table=blog_posts --force --routes
```

### Generate Model Only

```bash
php artisan crudly:model {ModelName}
```

### Generate Controller Only

```bash
php artisan crudly:controller {ControllerName} {ModelName}
```

---

## 🎨 Customize Generated Code

### Modify Views

Edit `resources/views/{plural-name}/`:

#### index.blade.php
```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Posts</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posts as $post)
                <tr>
                    <td>{{ $post->title }}</td>
                    <td><span class="badge">{{ $post->status }}</span></td>
                    <td>
                        <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
```

### Modify Controller

Edit `app/Http/Controllers/PostController.php`:

```php
public function index()
{
    // Add custom filtering
    $posts = Post::where('status', 'published')
        ->orderBy('created_at', 'desc')
        ->paginate(15);

    return view('posts.index', ['posts' => $posts]);
}

public function store(Request $request)
{
    // Add custom validation
    $validated = $request->validate([
        'title' => 'required|string|max:255|unique:posts',
        'content' => 'required|string|min:10',
        'slug' => 'required|string|unique:posts',
        'status' => 'required|in:draft,published',
    ]);

    // Add custom logic
    $validated['user_id'] = auth()->id();
    
    Post::create($validated);

    return redirect()->route('posts.index')
        ->with('success', 'Post created successfully!');
}
```

### Add Relationships

Edit `app/Models/Post.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'category_id', 'status'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

---

## ⚙️ Configuration

### Exclude Columns

In `config/crudly.php`:

```php
'table_filters' => [
    'users' => ['password', 'remember_token'],
    'posts' => ['ip_address'],
],
```

### Change CSS Framework

Update `.env`:
```env
CRUDLY_CSS_FRAMEWORK=bootstrap
```

Or `config/crudly.php`:
```php
'css_framework' => 'bootstrap', // or 'tailwind'
```

### Adjust Pagination

```env
CRUDLY_PAGINATION=25
```

### Change Route Prefix

```env
CRUDLY_ROUTE_PREFIX=admin
```

---

## 🚀 Advanced Features

### Using Crudly Facade

```php
use Shahrakii\Crudly\Facades\Crudly;

// Get table columns
$columns = Crudly::getTableColumns('posts');

// Get filtered columns
$filtered = Crudly::getFilteredColumns('posts');

// Get validation rules
$rules = Crudly::getValidationRules('posts');

// Get relationships
$relationships = Crudly::getRelationships('posts');

// Get all tables
$tables = Crudly::getAllTables();
```

### Custom Validation

In your controller:

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255|unique:posts',
        'slug' => [
            'required',
            'string',
            'unique:posts',
            'regex:/^[a-z0-9\-]+$/',
        ],
    ]);

    Post::create($validated);
    // ...
}
```

### Custom Authorization

Add to controller:

```php
public function __construct()
{
    $this->middleware('auth');
    $this->middleware('can:manage-posts');
}
```

---

## 🔍 Common Patterns

### Add Search Functionality

```php
public function index(Request $request)
{
    $query = Post::query();

    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('content', 'like', '%' . $request->search . '%');
    }

    $posts = $query->paginate(15);

    return view('posts.index', ['posts' => $posts]);
}
```

### Add Soft Deletes

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}
```

### Add Status Filtering

```php
public function index(Request $request)
{
    $query = Post::query();

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $posts = $query->paginate(15);

    return view('posts.index', [
        'posts' => $posts,
        'statuses' => ['draft', 'published', 'archived'],
    ]);
}
```

---

## ❓ FAQ

**Q: Can I customize the generated views?**
A: Yes! After generation, edit them freely. Crudly only creates them once.

**Q: Does Crudly support relationships?**
A: Yes! Foreign keys are automatically detected and validated.

**Q: Can I regenerate with `--force`?**
A: Yes, use `php artisan crudly:generate Post --force` to overwrite.

**Q: How do I add custom columns to views?**
A: Edit the views after generation. They're standard Blade templates.

**Q: Does Crudly generate migrations?**
A: No, create migrations first, then generate CRUD.

---

## 🆘 Troubleshooting

### Table not found

```bash
php artisan migrate
```

### Validation errors

Check `config/crudly.php` for excluded columns.

### Views not working

Ensure layout exists at `resources/views/layouts/app.blade.php`.

### Routes not working

Add to `routes/web.php`:
```php
Route::resource('posts', PostController::class);
```

---

For more help, visit the [GitHub repository](https://github.com/Shahrakii/Crudly) or check [INSTALLATION.md](INSTALLATION.md).
