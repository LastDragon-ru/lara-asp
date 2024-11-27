<!-- Generated automatically. Do not edit. -->

# `lara-asp-documentator:preprocess`

Perform one or more task on the file.

## Usages

* `artisan lara-asp-documentator:preprocess [--exclude [EXCLUDE]] [--] [<path>]`

## Description

## Tasks

### Preprocess (`md`)

Replaces special instructions in Markdown. Instruction is the [link
reference definition](https://github.github.com/gfm/#link-reference-definitions),
so the syntax is:

```plain
[<instruction>]: <target>
[<instruction>]: <target> (<parameters>)
[<instruction>=name]: <target>
[<instruction>=name]: <target> (<parameters>)
```

Where:

* `<instruction>` the instruction name (unknown instructions will be ignored)
* `<target>` usually the path to the file or directory, but see the instruction description
* `<parameters>` optional JSON string with additional parameters (can be
   wrapped by `(...)`, `"..."`, or `'...'`). The [Serializer](../../../serializer/README.md)
   package is used for deserialization.

#### Limitations

* Nested `<instruction>` not supported.

#### `[include:artisan]: <target>`

* `<target>` - Artisan command. The following special variables supported:

  * `{$directory}` - path of the directory where the file is located.
  * `{$file}` - path of the file.

Executes the `<target>` as Artisan command and returns result.

Please note that the working directory will not be changed to the file
directory (like `include:exec` do). This behavior is close to how Artisan
normally works (I'm also not sure that it is possible to change the current
working directory in any robust way when you call Artisan command from code).
You can use one of the special variables inside command args instead.

Also, the command will not inherit the current verbosity level, it will be
run with default/normal level if it is not specified in its arguments.

#### `[include:docblock]: <target> <parameters>`

* `<target>` - File path.
* `<parameters>` - additional parameters
  * `summary`: `bool` = `true` - Include the class summary?
  * `description`: `bool` = `true` - Include the class description?

Includes the docblock of the first PHP class/interface/trait/enum/etc
from `<target>` file. Inline tags include as is except `@see`/`@link`
which will be replaced to FQCN (if possible). Other tags are ignored.

#### `[include:document-list]: <target> <parameters>`

* `<target>` - Directory path.
* `<parameters>` - additional parameters
  * `depth`: `array|string|int|null` = `0` - [Directory Depth](https://symfony.com/doc/current/components/finder.html#directory-depth)
    (eg the `0` means no nested directories, the `null` removes limits).
  * `template`: `string` = `'default'` - Blade template. The documents passed in the `$data` ([`Data`][code-links/84d51020d324cc16])
    variable. Also, be careful with leading whitespaces.
  * `order`: [`SortOrder`][code-links/7e5c66e8748c6ff8] = [`SortOrder::Asc`][code-links/08e0648f66e2d1a5] - Sort order.
  * `level`: `?int` = `null` - Headings level. Possible values are

    * `null`: `<current level> + 1`
    * `int`: explicit level (`1-6`)
    * `0`: `<current level>`

Returns the list of `*.md` files in the `<target>` directory. Each file
must have `# Header` as the first construction. The first paragraph
after the Header will be used as a summary.

#### `[include:example]: <target>`

* `<target>` - File path.

Includes contents of the `<target>` file as an example wrapped into
` ```code block``` `. If [`Runner`][code-links/f9077a28b352f84b] bound, it will be called to execute
the example. Its return value will be added right after the code block.

By default, the `Runner` return value will be included as ` ```plain text``` `
block. You can wrap the output into `<markdown>text</markdown>` tags to
insert it as is.

#### `[include:exec]: <target>`

* `<target>` - Path to the executable.

Executes the `<target>` and returns result.

The working directory is equal to the file directory. If you want to run
Artisan command, please check `include:artisan` instruction.

#### `[include:file]: <target>`

* `<target>` - File path.

Includes the `<target>` file.

#### `[include:graphql-directive]: <target>`

* `<target>` - Directive name (started with `@` sign)

Includes the definition of the directive as a Markdown code block.

#### `[include:package-list]: <target> <parameters>`

* `<target>` - Directory path.
* `<parameters>` - additional parameters
  * `template`: `string` = `'default'` - Blade template.
  * `order`: [`SortOrder`][code-links/7e5c66e8748c6ff8] = [`SortOrder::Asc`][code-links/08e0648f66e2d1a5] - Sort order.

Generates package list from `<target>` directory. The readme file will be
used to determine package name and summary.

#### `[include:template]: <target> <parameters>`

* `<target>` - File path.
* `<parameters>` - additional parameters
  * `data`: `array` - Array of variables (`${name}`) to replace.

Includes the `<target>` as a template.

### Code Links (`md`)

Searches class/method/property/etc names in `inline code` and wrap it into a
link to file.

It expects that the `$root` directory is a composer project and will use
`psr-4` autoload rules to find class files. Classes which are not from the
composer will be completely ignored. If the file/class/method/etc doesn't
exist, the error will be thrown. To avoid the error, you can place `💀` mark
as the first character in `inline code`. Deprecated objects will be marked
automatically.

Supported links:

* `\App\Class`
* `\App\Class::method()`
* `\App\Class::$property`
* `\App\Class::Constant`

## Arguments

### `path?`

Directory to process.

## Options

### `--exclude=*`

Glob(s) to exclude.

[//]: # (start: code-links)
[//]: # (warning: Generated automatically. Do not edit.)

[code-links/84d51020d324cc16]: ../../src/Processor/Tasks/Preprocess/Instructions/IncludeDocumentList/Template/Data.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Data"

[code-links/f9077a28b352f84b]: ../../src/Processor/Tasks/Preprocess/Instructions/IncludeExample/Contracts/Runner.php
    "\LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeExample\Contracts\Runner"

[code-links/7e5c66e8748c6ff8]: ../../src/Utils/SortOrder.php
    "\LastDragon_ru\LaraASP\Documentator\Utils\SortOrder"

[code-links/08e0648f66e2d1a5]: ../../src/Utils/SortOrder.php#L6
    "\LastDragon_ru\LaraASP\Documentator\Utils\SortOrder::Asc"

[//]: # (end: code-links)
