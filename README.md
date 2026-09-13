<div align="center" style="text-align: center;">

![NAF](assets/naf-logo-small-square.png)

[![NAF View Plugin](https://github.com/nafphp/view/actions/workflows/php.yml/badge.svg)](https://github.com/nafphp/view/actions/workflows/php.yml)

</div>

[← Back to NAF](https://github.com/nafphp/framework)

---

# naf/view

> **A lightweight, native PHP templating system — with layout inheritance and block support.**

This plugin brings a clean, minimal templating system to your NAF application.
It lets you define base layouts, use content blocks, and safely output user data — all with pure PHP.

> 🧩 Part of the official NAF plugin collection.
> Install it when you need structured HTML rendering — without external engines like Twig or Blade.

---

## 📦 Features

* ✅ Define layouts and reuse views via `setLayout()` and `block()/endblock()`
* ✅ Render views with `view('template', [...])`
* ✅ Return response objects with `render('template', [...])`
* ✅ Safe output via `s()` (escape helper)
* ✅ Fully native PHP – no new syntax or templating engine required

---

## 📥 Installation

```bash
composer require naf/view
```

The plugin auto-registers itself and adds the `view()`, `render()`, `assets()` and `s()` helpers globally.

---

## 🚀 Usage

### 🧱 Rendering views

Use the `view()` helper to render a template and return the result as a **string**:

```php
$content = view('hello', ['name' => 'World']);
```

This is useful if you want to process or wrap the HTML manually.

Use the `render()` helper to return a **response object** instead (e.g. in your controller):

```php
return render('hello', ['name' => 'World']);
```

This renders the view **and wraps it in a proper response**, ready to be returned from any route handler.

To load a template file in another folder, you can use the dot notation:
```php
return render('pages.hello', ['name' => 'World']);
```

Or even multiple levels:

```php
return render('pages.elements.hello', ['name' => 'World']);
```

This works for both `view()` and `render()`.

---

### 🧩 Layouts & Blocks

Use `setLayout()` to define a parent layout, and `block()/endblock()` to inject content:

#### `views/page.phtml`

```php
<?php $this->setLayout('layout') ?>

<?php $this->block('content') ?>
    <h1>Hello <?= s($name) ?>!</h1>
<?php $this->endblock('content') ?>
```

#### `views/layout.phtml`

```php
<!doctype html>
<html>
<head>
    <title>My App</title>
</head>
<body>
    <?= $this->renderBlock('content') ?>
</body>
</html>
```

---

### 🛡️ Escape output

Use the `s()` helper to sanitize output (HTML-escaped):

```php
<p><?= s($userInput) ?></p>
```

---

## 🎨 Asset Management (CSS & JS)

The plugin includes a small, flexible asset collector used inside layouts to include CSS and JavaScript files.

Assets are added inside views or controllers using the `assets()` helper:

```php
assets()->add('/assets/style.css');            // CSS
assets()->add('/assets/app.js');               // JavaScript (classic)
assets()->add('/assets/main.js', 'module');    // JavaScript ES module
```

### Output in layout files

Use `assets()->render('css')` or `assets()->render('js')` inside your layout:

```php
<!doctype html>
<html>
<head>
    <?= assets()->render('css') ?>
</head>
<body>
    <?= $this->renderBlock('content') ?>
    <?= assets()->render('js') ?>
</body>
</html>
```

### What gets generated?

**CSS:**

```html
<link rel="stylesheet" href="/assets/style.css">
```

**Classic JS:**

```html
<script src="/assets/app.js"></script>
```

**Module JS:**

```html
<script type="module" src="/assets/main.js"></script>
```

### Internals

**All paths are automatically HTML-escaped via `s()`.**

---


## 🔁 Helper Comparison

| Helper     | Returns             | Use case                                |
| ---------- | ------------------- | --------------------------------------- |
| `render()` | `ResponseInterface` | Ideal for controller return values   |
| `view()`   | `string`            | For manual output or further processing |
| `assets()` | `string`            | Include CSS & JS files in layouts       |
| `s()`      | `string`            | Escape output                           |

---

## 🔍 Internals

* `view()` resolves and loads `.phtml` templates from the directories listed in `view:paths` (defaults to `views/` first with `app/views/` as a fallback) before checking any registered plugin or framework views.
* `setLayout()` nests the rendered content into a wrapper view.
* Blocks are buffered and stored internally until rendered.

### ⚙️ Configurable view locations

Set the `view:paths` configuration to control where templates are resolved inside your application. This plugin ships with `src/config.php`, which defaults to:

```php
return [
    'view' => [
        'paths' => [
            'views',
            'app/views',
        ],
    ],
];
```

The entries are resolved relative to `BASE_PATH` when they are not absolute paths, so you can place templates anywhere and order them however you need. The plugin checks each directory in order before falling back to registered plugin or framework view paths.

---

## ✅ Requirements

* `naf/framework` >= 1.0

---

## 📄 License

MIT License.