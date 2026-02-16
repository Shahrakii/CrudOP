# 📚 Crudly Documentation

Welcome to the Crudly documentation! This guide will help you master the CRUD generator.

## 📖 Documentation Files

### 🚀 Getting Started (Pick One)

| Document | Duration | Best For |
|----------|----------|----------|
| **[QUICK_START.md](QUICK_START.md)** | 5 min | **Fast learners** - Generate your first CRUD in minutes |
| **[INSTALLATION.md](INSTALLATION.md)** | 10 min | **Installation issues** - Detailed step-by-step setup |
| **[SETUP_GUIDE.md](SETUP_GUIDE.md)** | 30 min | **Complete beginners** - Full project setup from scratch |

### 📚 Learn & Master

| Document | Content |
|----------|---------|
| **[USAGE_GUIDE.md](USAGE_GUIDE.md)** | Detailed examples, commands, customization, advanced features |
| **[README.md](README.md)** | Features overview, API reference, contribution guide |

---

## 🎯 Quick Navigation

### I Want To...

#### ...Get Started ASAP (5 min)
→ Read **[QUICK_START.md](QUICK_START.md)**

```bash
php artisan crudly:generate Post --routes
```

#### ...Install Crudly (10 min)
→ Read **[INSTALLATION.md](INSTALLATION.md)**

```bash
composer install
php artisan vendor:publish --provider="Shahrakii\Crudly\CrudlyServiceProvider"
```

#### ...Learn Everything (Full Guide)
→ Read **[SETUP_GUIDE.md](SETUP_GUIDE.md)**

#### ...See Code Examples
→ Read **[USAGE_GUIDE.md](USAGE_GUIDE.md)**

#### ...Troubleshoot Issues
→ Check [INSTALLATION.md](INSTALLATION.md) → Troubleshooting section

---

## 🔥 Command Cheat Sheet

```bash
# Generate complete CRUD
php artisan crudly:generate Post --routes

# Generate with custom table
php artisan crudly:generate Post --table=blog_posts --routes

# Overwrite existing files
php artisan crudly:generate Post --force

# Generate model only
php artisan crudly:model Post

# Generate controller only
php artisan crudly:controller PostController Post

# List all commands
php artisan list crudly
```

---

## 📦 What's Included

```
Crudly/
├── docs/
│   ├── README.md           ← Features & API
│   ├── QUICK_START.md      ← 5 min setup
│   ├── INSTALLATION.md     ← Detailed install
│   ├── SETUP_GUIDE.md      ← Complete setup
│   ├── USAGE_GUIDE.md      ← Examples & patterns
│   └── INDEX.md            ← This file
├── crudly/
│   ├── src/
│   │   ├── Crudly.php
│   │   ├── CrudlyServiceProvider.php
│   │   ├── Facades/
│   │   ├── Helpers/
│   │   ├── Console/Commands/
│   │   └── Http/Controllers/
│   ├── config/
│   ├── routes/
│   ├── resources/views/
│   ├── tests/
│   ├── composer.json
│   ├── LICENSE
│   └── README.md
└── SETUP.md (This directory overview)
```

---

## 🎓 Learning Path

### Beginner
1. Start: [QUICK_START.md](QUICK_START.md) - 5 minutes
2. Install: [INSTALLATION.md](INSTALLATION.md) - 10 minutes
3. Setup: [SETUP_GUIDE.md](SETUP_GUIDE.md) - 30 minutes
4. Generate first CRUD ✅

### Intermediate
1. Read: [USAGE_GUIDE.md](USAGE_GUIDE.md) - Examples & patterns
2. Customize: Generated code
3. Add: Relationships & logic
4. Deploy: To production

### Advanced
1. Extend: Create custom commands
2. Contribute: Fork on GitHub
3. Publish: Your own packages
4. Optimize: For your use case

---

## ✨ Key Features

- ✅ **Auto-generates** Controllers, Models, Views
- ✅ **Smart validation** rules from database schema
- ✅ **Relationship support** (Foreign keys, Enums)
- ✅ **Multiple CSS frameworks** (Tailwind, Bootstrap)
- ✅ **Professional views** with pagination
- ✅ **Customizable** everything
- ✅ **Production ready** code

---

## 🚀 30-Second Setup

```bash
# 1. Install
composer install

# 2. Create table
php artisan make:migration create_posts_table
# Edit migration, then:
php artisan migrate

# 3. Generate
php artisan crudly:generate Post --routes

# 4. Test
php artisan serve
# Visit: http://localhost:8000/posts
```

✅ Done! CRUD is ready!

---

## 📞 Getting Help

### Documentation
- 📖 Read the docs above
- 🔍 Check [USAGE_GUIDE.md](USAGE_GUIDE.md) for examples
- ❓ See FAQ section

### Issues
- 🐛 Check [INSTALLATION.md](INSTALLATION.md) → Troubleshooting
- 💬 Open GitHub issue
- 📧 Email support

---

## 🤝 Contributing

Want to improve Crudly?

1. Fork repository
2. Create feature branch
3. Make changes
4. Submit pull request

See [README.md](README.md) → Contributing section

---

## 📄 License

Crudly is licensed under the **MIT License**.
See LICENSE file in crudly directory.

---

## 🎉 Ready?

Choose your starting point:

| If You're... | Start Here |
|--------------|-----------|
| **Impatient** ⚡ | [QUICK_START.md](QUICK_START.md) |
| **New to Crudly** 🆕 | [SETUP_GUIDE.md](SETUP_GUIDE.md) |
| **Troubleshooting** 🔧 | [INSTALLATION.md](INSTALLATION.md) |
| **Learning Examples** 📚 | [USAGE_GUIDE.md](USAGE_GUIDE.md) |
| **Want Details** 📖 | [README.md](README.md) |

---

**Let's build something amazing! 🚀**
