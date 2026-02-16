# 🎓 Crudly Complete Setup Guide

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Project Setup](#project-setup)
3. [Crudly Installation](#crudly-installation)
4. [Database Setup](#database-setup)
5. [First CRUD Generation](#first-crud-generation)
6. [Customization](#customization)
7. [Deployment](#deployment)

---

## System Requirements

### Required
- **PHP:** 8.2 or higher
- **Laravel:** 12.x
- **Composer:** Latest version
- **Database:** MySQL 5.7+, PostgreSQL 9.4+, SQLite 3, SQL Server

### Optional
- **Node.js:** For asset compilation
- **Docker:** For containerized development

---

## Project Setup

### 1. Create New Laravel Project

```bash
# Using Laravel Installer
laravel new my-app
cd my-app

# OR using Composer
composer create-project laravel/laravel my-app
cd my-app
```

### 2. Configure Environment

Edit `.env`:

```env
APP_NAME=MyApp
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_app
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Create Database

```bash
# MySQL
mysql -u root -p
CREATE DATABASE my_app;

# Or use Laravel command
php artisan db:create
```

### 4. Generate App Key

```bash
php artisan key:generate
```

---

## Crudly Installation

### Method 1: Local Development (Path Repository)

**Best for development and contribution**

```bash
# 1. Create directory
mkdir -p packages/shahrakii/crudly

# 2. Extract Crudly files into packages/shahrakii/crudly/

# 3. Update composer.json
```

Edit `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/shahrakii/crudly"
        }
    ],
    "require": {
        "shahrakii/crudly": "@dev"
    }
}
```

```bash
# 4. Install
composer install
composer dump-autoload
```

### Method 2: Packagist (Production)

**Once published to Packagist**

```bash
composer require shahrakii/crudly
```

### Method 3: GitHub (Using VCS)

```bash
# Edit composer.json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Shahrakii/Crudly"
        }
    ],
    "require": {
        "shahrakii/crudly": "dev-main"
    }
}

composer install
```

### Verify Installation

```bash
# Check service provider is registered
php artisan vendor:publish --list

# Check routes
php artisan route:list | grep crudly

# Check commands
php artisan list crudly

# Test
php artisan serve
# Visit http://localhost:8000/crudly/check
```

---

## Database Setup

### Create Your First Table

#### Option 1: Using Migrations

```bash
php artisan make:migration create_products_table
```

Edit `database/migrations/YYYY_MM_DD_create_products_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

Run migration:

```bash
php artisan migrate
```

#### Option 2: Manual Database Setup

If using existing database:

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    sku VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## First CRUD Generation

### Generate CRUD

```bash
php artisan crudly:generate Product --routes
```

**Output:**
```
🚀 Generating CRUD for Product...

✅ Generated Model: Product
✅ Generated Controller: ProductController
✅ Generated Views
✅ Routes added

✨ CRUD generation complete!
Run: php artisan serve
```

### What Was Created

```
app/
├── Models/
│   └── Product.php                    ← Model
└── Http/Controllers/
    └── ProductController.php           ← Controller

resources/views/
└── products/
    ├── index.blade.php                ← List view
    ├── create.blade.php               ← Create form
    ├── edit.blade.php                 ← Edit form
    └── show.blade.php                 ← Detail view
```

### Test Your CRUD

```bash
php artisan serve
```

Open browser:

| Action | URL |
|--------|-----|
| List Products | http://localhost:8000/products |
| Add Product | http://localhost:8000/products/create |
| Edit Product | http://localhost:8000/products/1/edit |
| View Product | http://localhost:8000/products/1 |
| Delete Product | Click delete on any item |

---

## Customization

### 1. Modify Controller

Edit `app/Http/Controllers/ProductController.php`:

```php
public function index()
{
    $products = Product::where('status', 'active')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return view('products.index', ['products' => $products]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:products',
        'sku' => 'required|string|unique:products',
        'price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:0',
        'status' => 'required|in:active,inactive',
    ]);

    Product::create($validated);

    return redirect()->route('products.index')
        ->with('success', 'Product created successfully!');
}
```

### 2. Modify Views

Edit `resources/views/products/index.blade.php`:

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Products</h1>
        <a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
            Add Product
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border p-2 text-left">Name</th>
                    <th class="border p-2 text-left">Price</th>
                    <th class="border p-2 text-left">Stock</th>
                    <th class="border p-2 text-left">Status</th>
                    <th class="border p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="border p-2">{{ $product->name }}</td>
                        <td class="border p-2">${{ number_format($product->price, 2) }}</td>
                        <td class="border p-2">{{ $product->stock }}</td>
                        <td class="border p-2">
                            <span class="px-2 py-1 rounded text-white {{ $product->status === 'active' ? 'bg-green-500' : 'bg-red-500' }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="border p-2">
                            <a href="{{ route('products.show', $product) }}" class="text-blue-500 hover:underline">View</a> |
                            <a href="{{ route('products.edit', $product) }}" class="text-yellow-500 hover:underline">Edit</a> |
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete?')" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="border p-4 text-center text-gray-500">No products found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
@endsection
```

### 3. Add Model Relationships

Edit `app/Models/Product.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = ['name', 'sku', 'description', 'price', 'stock', 'status'];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

### 4. Update Configuration

Edit `config/crudly.php`:

```php
return [
    'pagination' => 20,
    'css_framework' => 'tailwind',
    'global_filters' => ['id', 'created_at', 'updated_at'],
];
```

---

## Deployment

### 1. Prepare for Production

```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate app key
php artisan key:generate
```

### 2. Environment Setup

Create `.env`:

```env
APP_NAME=MyApp
APP_ENV=production
APP_DEBUG=false
APP_URL=https://myapp.com

DB_CONNECTION=mysql
DB_HOST=db.example.com
DB_PORT=3306
DB_DATABASE=myapp_prod
DB_USERNAME=user
DB_PASSWORD=secure_password
```

### 3. Run Migrations

```bash
php artisan migrate --force
```

### 4. Deploy

```bash
# Using traditional hosting
# Upload files via FTP/SFTP

# Using Docker
docker build -t myapp .
docker run -p 80:8000 myapp

# Using Heroku
heroku login
heroku create myapp
git push heroku main

# Using Laravel Forge
# (Configure via dashboard)
```

---

## ✅ Checklist

- [ ] PHP 8.2+ installed
- [ ] Laravel 12 project created
- [ ] Database configured
- [ ] Crudly installed
- [ ] Database tables created
- [ ] CRUD generated with `--routes`
- [ ] Views customized
- [ ] Controller logic updated
- [ ] Tested all CRUD operations
- [ ] Ready for production

---

## 🆘 Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Table doesn't exist" | Run `php artisan migrate` |
| "Class not found" | Run `composer dump-autoload` |
| "Route not found" | Check `routes/web.php` has `Route::resource('products', ProductController::class);` |
| "Views not loading" | Check layout at `resources/views/layouts/app.blade.php` exists |
| "Database connection error" | Verify `.env` database credentials |

---

## 📚 Next Steps

1. **Read:** [QUICK_START.md](QUICK_START.md) - 5-minute overview
2. **Learn:** [USAGE_GUIDE.md](USAGE_GUIDE.md) - Detailed examples
3. **Reference:** [README.md](README.md) - Complete documentation
4. **Customize:** Your generated code
5. **Deploy:** To production

---

**You're all set! Happy coding with Crudly! 🚀**
