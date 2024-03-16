<!-- Generated automatically. Do not edit. -->

# `lara-asp-documentator:requirements`

Generates a table with the required versions of PHP/Laravel in Markdown format.

## Usages

* `artisan lara-asp-documentator:requirements [<cwd>]`

## Description

Requirements will be cached into `<cwd>/metadata.json`. You can also use
this file to specify the required requirements. For example, to include
PHP only:

```json
{
    "require": {
        "php": "PHP"
    }
}
```

You can also merge multiple requirements into one. For example, the
following will merge all `illuminate` into `laravel/framework` (the
package will be ignored if not listed in `require`):

```json
{
    "merge": {
        "illuminate/*": "laravel/framework"
    }
}
```

## Arguments

### `cwd?`

working directory (should be a git repository)
