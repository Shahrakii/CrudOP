# ⚡ Crudly Quick Start (5 Minutes)

## 🎯 Goal
Generate a complete CRUD application in 5 minutes!

## Step 1: Installation (1 minute)

```bash
# Option 1: Local (Development)
composer require --path=packages/shahrakii/crudly

# Option 2: Via Composer (Production)
composer require shahrakii/crudly
```

Verify:
```bash
php artisan route:list | grep crudly
```

## Step 2: Create Database (2 minutes)

Create `.env` database connection:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_app
DB_USERNAME=root
DB_PASSWORD=
```

Create migration:
```bash
php artisan make:migration create_posts_table
```

Edit migration:
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->timestamps();
});
```

Run:
```bash
php artisan migrate
```

## Step 3: Generate CRUD (1 minute)

```bash
php artisan crudly:generate Post --routes
```

## Step 4: Test (1 minute)

```bash
php artisan serve
```

Visit:
- **List:** http://localhost:8000/posts
- **Create:** http://localhost:8000/posts/create
- **Edit:** http://localhost:8000/posts/1/edit

---

## ✅ Done!

Your complete CRUD is ready to use!

### What Was Created:
- ✅ Model: `app/Models/Post.php`
- ✅ Controller: `app/Http/Controllers/PostController.php`
- ✅ Views: `resources/views/posts/` (index, create, edit, show)
- ✅ Routes: Added to `routes/web.php`
- ✅ Validation: Automatic based on schema

### Next Steps:
1. Customize views in `resources/views/posts/`
2. Add more logic to controller
3. Add relationships to model
4. Deploy!

---

## 🚀 Generate More CRUD

```bash
# Generate for another table
php artisan crudly:generate Product --routes
php artisan crudly:generate Category --routes
```

Each takes < 30 seconds!

---

## 📚 Learn More

- **Installation:** See [INSTALLATION.md](INSTALLATION.md)
- **Complete Guide:** See [USAGE_GUIDE.md](USAGE_GUIDE.md)
- **Configuration:** See [README.md](README.md)

---

## 💡 Pro Tips

| Tip | Command |
|-----|---------|
| Overwrite existing | `--force` |
| Custom table name | `--table=blog_posts` |
| Skip routes | Remove `--routes` |
| Model only | `php artisan crudly:model Post` |

---

Enjoy rapid development with Crudly! 🎉
