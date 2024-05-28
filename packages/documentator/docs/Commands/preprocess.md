<!-- Generated automatically. Do not edit. -->

# `lara-asp-documentator:preprocess`

Preprocess Markdown files.

## Usages

* `artisan lara-asp-documentator:preprocess [--exclude [EXCLUDE]] [--] [<path>]`

## Description

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
* `<parameters>` optional JSON string with additional parameters
  (can be wrapped by `(...)`, `"..."`, or `'...'`)

## Limitations

* `<instruction>` will be processed everywhere in the file (eg within
  the code block) and may give unpredictable results.
* `<instruction>` cannot be inside text.
* Nested `<instruction>` doesn't support.

## Instructions

### `[include:artisan]: <target>`

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

### `[include:docblock]: <target> <parameters>`

* `<target>` - File path.
* `<parameters>` - additional parameters
  * `summary: bool = true` - Include the class summary?
  * `description: bool = true` - Include the class description?

Includes the docblock of the first PHP class/interface/trait/enum/etc
from `<target>` file. Inline tags include as is except `@see`/`@link`
which will be replaced to FQCN (if possible). Other tags are ignored.

### `[include:document-list]: <target> <parameters>`

* `<target>` - Directory path.
* `<parameters>` - additional parameters
  * `depth: array|string|int|null = 0` - [Directory Depth](https://symfony.com/doc/current/components/finder.html#directory-depth)
    (eg the `0` means no nested directories, the `null` removes limits).
  * `template: string = 'default'` - Blade template.

Returns the list of `*.md` files in the `<target>` directory. Each file
must have `# Header` as the first construction. The first paragraph
after the Header will be used as a summary.

### `[include:example]: <target>`

* `<target>` - File path.

Includes contents of the `<target>` file as an example wrapped into
` ```code block``` `. If {@see Runner} bound, it will be called to execute
the example. Its return value will be added right after the code block.

By default, the `Runner` return value will be included as ` ```plain text``` `
block. You can wrap the output into `<markdown>text</markdown>` tags to
insert it as is.

### `[include:exec]: <target>`

* `<target>` - Path to the executable.

Executes the `<target>` and returns result.

The working directory is equal to the file directory. If you want to run
Artisan command, please check `include:artisan` instruction.

### `[include:file]: <target>`

* `<target>` - File path.

Includes the `<target>` file.

### `[include:graphql-directive]: <target>`

* `<target>` - Directive name (started with `@` sign)

Includes the definition of the directive as a Markdown code block.

### `[include:package-list]: <target> <parameters>`

* `<target>` - Directory path.
* `<parameters>` - additional parameters
  * `template: string = 'default'` - Blade template.

Generates package list from `<target>` directory. The readme file will be
used to determine package name and summary.

### `[include:template]: <target> <parameters>`

* `<target>` - File path.
* `<parameters>` - additional parameters
  * `data: array` - Array of variables (`${name}`) to replace.

Includes the `<target>` as a template.

## Arguments

### `path?`

Directory to process.

## Options

### `--exclude=*`

Glob(s) to exclude.
