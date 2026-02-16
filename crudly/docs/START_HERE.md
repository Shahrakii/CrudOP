# 🎉 Crudly - Complete Package

This is the **COMPLETE, PRODUCTION-READY** version of Crudly - an intelligent CRUD generator for Laravel 12.

## 📦 What's Inside

```
Crudly-Complete/
├── crudly/                     ← The complete package
│   ├── src/                    ← Source code
│   ├── config/                 ← Configuration files
│   ├── routes/                 ← Package routes
│   ├── resources/views/        ← Blade templates
│   ├── composer.json           ← Package configuration
│   ├── LICENSE                 ← MIT License
│   └── README.md               ← Package documentation
│
└── docs/                       ← Complete documentation
    ├── INDEX.md                ← Documentation index (START HERE!)
    ├── QUICK_START.md          ← 5-minute setup
    ├── INSTALLATION.md         ← Detailed installation
    ├── SETUP_GUIDE.md          ← Complete project setup
    ├── USAGE_GUIDE.md          ← Examples & patterns
    └── README.md               ← Features & reference
```

## 🚀 Quick Start (Choose Your Path)

### ⚡ Super Fast (5 minutes)
```bash
# 1. Read this
cat docs/QUICK_START.md

# 2. Install Crudly
composer install

# 3. Generate CRUD
php artisan crudly:generate Post --routes

# 4. Done!
php artisan serve
```

### 📚 Complete Setup (30 minutes)
```bash
# Follow the step-by-step guide
cat docs/SETUP_GUIDE.md
```

### 🔧 Troubleshooting
```bash
# If you have issues
cat docs/INSTALLATION.md
```

---

## 📖 Documentation

| Document | Duration | Purpose |
|----------|----------|---------|
| **docs/INDEX.md** | 5 min | **START HERE** - Navigation guide |
| **docs/QUICK_START.md** | 5 min | Get CRUD working in 5 minutes |
| **docs/INSTALLATION.md** | 10 min | Install Crudly step-by-step |
| **docs/SETUP_GUIDE.md** | 30 min | Complete project setup guide |
| **docs/USAGE_GUIDE.md** | 45 min | Examples, patterns, customization |
| **docs/README.md** | Reference | Complete features & API reference |

---

## 🎯 What Crudly Does

Crudly automatically generates:

- ✅ **Laravel Model** with proper namespaces
- ✅ **RESTful Controller** with CRUD methods
- ✅ **4 Blade Views** (index, create, edit, show)
- ✅ **Validation Rules** from database schema
- ✅ **Relationship Support** (Foreign keys, Enums)
- ✅ **Routes** (optional, auto-added)

**All in ONE command:**
```bash
php artisan crudly:generate Post --routes
```

---

## 📋 Installation Methods

### Method 1: Local Development (Recommended)

```bash
# 1. Create package directory
mkdir -p packages/shahrakii/crudly

# 2. Extract crudly folder contents into packages/shahrakii/crudly/

# 3. Update composer.json with path repository
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

# 4. Install
composer install
composer dump-autoload
```

### Method 2: Git Repository (Once Published)

```bash
composer require shahrakii/crudly
```

### Method 3: Manual Integration

Copy the `crudly/src` folder to your Laravel app.

---

## ✨ Features

### Smart Schema Detection
- Automatically detects column types (string, integer, date, etc.)
- Handles relationships (foreign keys)
- Supports enums
- Extracts validation requirements

### Intelligent Code Generation
- Professional, commented code
- Follows Laravel conventions
- Uses Eloquent relationships
- Includes error handling

### Customizable Output
- Choose CSS framework (Tailwind/Bootstrap)
- Customize validation rules
- Extend generated classes
- Modify views easily

### Developer Friendly
- Simple one-command generation
- Clear, readable generated code
- Extensive documentation
- Active support

---

## 🎓 Learning Path

### Absolute Beginner
1. Read: `docs/QUICK_START.md` (5 min)
2. Read: `docs/INSTALLATION.md` (10 min)
3. Generate first CRUD (5 min)
4. Success! ✅

### Want to Learn More
1. Read: `docs/USAGE_GUIDE.md` (45 min)
2. Customize generated code
3. Add relationships
4. Deploy

### Advanced Users
1. Read: `docs/README.md` (reference)
2. Extend Crudly
3. Create custom commands
4. Contribute back

---

## 🔥 Command Reference

```bash
# Generate complete CRUD (with routes)
php artisan crudly:generate Post --routes

# Generate without routes
php artisan crudly:generate Post

# Generate with custom table name
php artisan crudly:generate Post --table=blog_posts --routes

# Regenerate (overwrite existing)
php artisan crudly:generate Post --force --routes

# Generate model only
php artisan crudly:model Post

# List all Crudly commands
php artisan list crudly
```

---

## 🛠️ Configuration

Edit `config/crudly.php`:

```php
return [
    'pagination' => 15,              // Items per page
    'css_framework' => 'tailwind',   // tailwind or bootstrap
    'route_prefix' => 'admin',       // Route prefix
    'middleware' => ['web', 'auth'], // Middleware
];
```

---

## 📊 Example Usage

### 1. Create Migration
```bash
php artisan make:migration create_products_table
```

Edit migration:
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->decimal('price', 10, 2);
    $table->integer('stock');
    $table->timestamps();
});
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Generate CRUD
```bash
php artisan crudly:generate Product --routes
```

### 4. Test
```bash
php artisan serve
# Visit: http://localhost:8000/products
```

✅ **Done!** Full CRUD is working!

---

## 🆘 Troubleshooting

### "Table doesn't exist"
```bash
php artisan migrate
```

### "Class not found"
```bash
composer dump-autoload
php artisan clear
```

### "Routes not working"
Ensure `Route::resource('products', ProductController::class);` is in `routes/web.php`

### "Views not loading"
Check `resources/views/layouts/app.blade.php` exists

For more help, see: `docs/INSTALLATION.md` → Troubleshooting

---

## 📝 Files Structure

```
crudly/
├── src/
│   ├── Crudly.php                              ← Main class
│   ├── CrudlyServiceProvider.php               ← Service provider
│   ├── Facades/
│   │   └── Crudly.php                          ← Facade
│   ├── Helpers/
│   │   ├── SchemaExtractor.php                 ← Schema introspection
│   │   └── ValidationGenerator.php             ← Rules generation
│   ├── Console/Commands/
│   │   ├── GenerateCrudCommand.php             ← Main command
│   │   ├── GenerateModelCommand.php            ← Model generation
│   │   └── GenerateControllerCommand.php       ← Controller generation
│   ├── Http/Controllers/
│   │   └── CrudController.php                  ← Base CRUD controller
│   └── Traits/
│       └── HasCrudTrait.php                    ← Reusable traits
├── config/
│   └── crudly.php                              ← Configuration
├── routes/
│   └── web.php                                 ← Package routes
├── resources/views/                            ← Default views
├── tests/                                      ← Test suite
├── composer.json                               ← Package config
├── LICENSE                                     ← MIT License
└── README.md                                   ← Package README
```

---

## 🚀 Next Steps

1. **Read Documentation**: Start with `docs/INDEX.md`
2. **Install**: Follow `docs/INSTALLATION.md`
3. **Quick Test**: Follow `docs/QUICK_START.md`
4. **Learn**: Read `docs/USAGE_GUIDE.md`
5. **Build**: Create your CRUD operations
6. **Customize**: Modify for your needs
7. **Deploy**: Push to production

---

## 💡 Pro Tips

| Tip | Benefit |
|-----|---------|
| Use `--routes` flag | Automatically adds routes to routes/web.php |
| Use `--force` flag | Regenerate if needed |
| Customize after generation | Generated code is yours to modify |
| Read source code | Learn Laravel patterns |
| Use Facade | Easy access to helpers |

---

## 📚 Resources

- **Documentation**: See `/docs` folder
- **Source Code**: See `/crudly/src` folder
- **Configuration**: Edit `config/crudly.php`
- **Tests**: See `/crudly/tests` folder

---

## 🎯 Who Should Use Crudly?

✅ **Laravel developers** - Save time generating CRUD
✅ **Beginners** - Learn Laravel patterns
✅ **Agencies** - Rapid prototyping
✅ **Startups** - Quick MVP development
✅ **Teams** - Consistent code generation
✅ **Anyone** - Building data-driven apps

---

## 📊 Performance

- **Generation Time**: < 1 second per CRUD
- **Generated Code**: ~500 lines (model + controller + views)
- **Database**: Works with MySQL, PostgreSQL, SQLite, SQL Server
- **Laravel**: Compatible with Laravel 12.x

---

## 📄 License

Crudly is licensed under the **MIT License**.
You're free to use, modify, and distribute.

See `crudly/LICENSE` for details.

---

## 🎉 You're Ready!

### Choose Your Starting Point:

| You Want... | Read This |
|-------------|-----------|
| **Quick demo** | `docs/QUICK_START.md` |
| **Full setup** | `docs/SETUP_GUIDE.md` |
| **Installation help** | `docs/INSTALLATION.md` |
| **Code examples** | `docs/USAGE_GUIDE.md` |
| **Complete reference** | `docs/README.md` |
| **Navigation guide** | `docs/INDEX.md` |

---

## 🚀 Let's Build Something Amazing!

**Crudly - Making Laravel Development Faster, Easier, and Better**

---

**Questions?** Check the documentation or open an issue.

**Want to contribute?** Fork the repository and submit a PR.

**Enjoy!** 🎉
