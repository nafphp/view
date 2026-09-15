# Working on naf/view

NAF is a small PHP framework with optional Composer plugins. Its core owns boot,
configuration, the service container, routing, events and PSR-7 responses. Prefer existing
NAF helpers, services and extension interfaces; keep application business rules in the host.
This package declares `type: naf-plugin` and is discovered after installation in a NAF host.
The plugin repository itself is not the application's web root.

Before changing code, read the [shared contribution workflow](https://github.com/nafphp/docs/blob/main/AGENT_WORKFLOW.md)
and [release procedure](https://github.com/nafphp/docs/blob/main/RELEASING.md).
In the multi-repository workspace, the same documents are in the sibling `docs/` checkout;
use the linked copies when working from a standalone clone. Preserve other contributors' work.
Review and update user documentation with every behavior change. Source fixes use an RC branch;
verified documentation-only changes can be merged and published by the agent.

## What this plugin does

`naf/view` provides `.phtml` rendering, layout/block support, view-path overrides and assets.
Install with `composer require naf/view`. Import `render`, `view`, `s` and `asset` from
`Naf\View`; these are not global or core-namespace functions.

## Use it

A handler can return the rendered response:

```php
<?php
use function Naf\View\render;

// Inside a routed controller action; requires app/views/greeting.phtml:
return render('greeting', ['name' => 'Ada']);
```

The template `app/views/greeting.phtml`:

```php
<?php use function Naf\View\s; ?>
<p>Hello <?= s($name) ?></p>
```

`render()` returns a PSR-7 response; `view()` returns a string. Escape untrusted HTML text and
quoted attribute values with `s()`. It is not a JavaScript/CSS/URL sanitizer. Rendering uses
normal PHP; variables are not automatically escaped.

## Change it here

[View](src/Core/View.php) owns paths, templates and blocks; [Asset](src/Core/Asset.php) owns
asset behavior; [helpers](src/view_helpers.php), [config](src/config.php) and [bootstrap](bootstrap.php)
wire the plugin. Host paths from `view:paths` precede plugin templates; use those extension
points instead of editing installed vendor files. Follow existing block/layout conventions
rather than assuming another engine's `$content` variable or component syntax.
Do not weaken template-path validation. Guard-dependent rendering needs a booted HTTP
application or the appropriate guards in a CLI test harness.

## Verify

Run `composer test` and `composer validate --strict`. Use [tests](tests/) and fixture templates
for rendering, overrides, escaping, path rejection, blocks and assets. Check visible HTML in
an actual host when changing template behavior. No `analyse` script is declared.

User docs: [Views and assets](https://nafphp.github.io/docs/views/).

Follow the shared [PHP code style](https://github.com/nafphp/docs/blob/main/CODE_STYLE.md)
and `.php-cs-fixer.dist.php`. Run `composer style:check`; `composer style:fix` applies the rules.
Keep logical steps and local names readable, preserving public signatures and template output.
