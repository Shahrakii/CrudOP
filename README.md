# 📦 Crudly Installation Guide

## Step-by-Step Installation

### Step 1: Add Crudly to Your Project

#### Option A: Using Path Repository (Local Development)

Add to `composer.json`:

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

Create directory:
```bash
mkdir -p packages/shahrakii/crudly
```

Extract Crudly files into `packages/shahrakii/crudly/`

#### Option B: Using Composer (Once Published)

```bash
composer require shahrakii/crudly
```

### Step 2: Install Dependencies

```bash
composer install
composer dump-autoload
```

### Step 3: Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Shahrakii\Crudly\CrudlyServiceProvider" --tag="crudly-config"
```

This creates `config/crudly.php` for customization.

### Step 4: Verify Installation

```bash
php artisan route:list | grep crudly
php artisan list crudly
```

You should see:
- Route: `GET|HEAD   /crudly/check`
- Commands: `crudly:generate`, `crudly:model`, `crudly:controller`

### Step 5: Test Installation

```bash
php artisan serve
```

Visit: `http://localhost:8000/crudly/check`

You should see:
```json
{
    "status": "success",
    "message": "Crudly is working!",
    "version": "1.0.0"
}
```

---

## ✅ Troubleshooting

### Issue: Class not found

**Solution:**
```bash
composer dump-autoload
composer clear-cache
```

### Issue: Routes not loading

**Solution:**
Ensure `CrudlyServiceProvider` is auto-discovered:
```bash
php artisan vendor:publish --provider="Shahrakii\Crudly\CrudlyServiceProvider"
```

### Issue: Views not found

**Solution:**
Publish views:
```bash
php artisan vendor:publish --provider="Shahrakii\Crudly\CrudlyServiceProvider" --tag="crudly-views"
```

### Issue: Table not found

**Solution:**
- Ensure migrations are run: `php artisan migrate`
- Verify table exists: `php artisan tinker` → `Schema::hasTable('posts')`

---

## 🎯 Next Steps

1. Create a database table
2. Run migrations
3. Generate CRUD: `php artisan crudly:generate Post`
4. Add routes: `Route::resource('posts', PostController::class);`
5. Visit your CRUD interface

See [USAGE_GUIDE.md](USAGE_GUIDE.md) for detailed examples.
