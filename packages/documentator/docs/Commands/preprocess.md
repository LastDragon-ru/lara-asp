<!-- Generated automatically. Do not edit. -->

# `lara-asp-documentator:preprocess`

Preprocess Markdown files.

## Usages

* `artisan lara-asp-documentator:preprocess [<path>]`

## Description

Replaces special instructions in Markdown. Instruction is the [link
reference definition](https://github.github.com/gfm/#link-reference-definitions),
so the syntax is:

```plain
[<instruction>]: <target>
[<instruction>]: <target> (<parameters>)
[<instruction>=name]: <target>
```

Where:

* `<instruction>` the instruction name (unknown instructions will be ignored)
* `<target>` usually the path to the file or directory, but see the instruction description
* `<parameters>` optional JSON string with additional parameters
    (can be wrapped by `(...)`, `"..."`, or `'...'`)

## Instructions

### `[include:docblock]: <target> <parameters>`

* `<target>` - File path.
* `<parameters>` - additional parameters
  * `summary` - Include the class summary? (default `false`)
  * `description` - Include the class description? (default `true`)

Includes the docblock of the first PHP class/interface/trait/enum/etc
from `<target>` file. Inline tags include as is. Other tags are
ignored.

### `[include:document-list]: <target> <parameters>`

* `<target>` - Directory path.
* `<parameters>` - additional parameters
  * `depth` - Default is `0` (no nested directories). The `null` removes limits.
  * `template` - Blade template

Returns the list of `*.md` files in the `<target>` directory. Each file
must have `# Header` as the first construction. The first paragraph
after the Header will be used as a summary.

### `[include:example]: <target>`

* `<target>` - Example file path.

Includes contents of the `<target>` file as an example wrapped into
` ```code block``` `. It also searches for `<target>.run` file, execute
it if found, and include its result right after the code block.

By default, output of `<target>.run` will be included as ` ```plain text``` `
block. You can wrap the output into `<markdown>text</markdown>` tags to
insert it as is.

### `[include:exec]: <target>`

* `<target>` - Path to the executable.

Executes the `<target>` and returns result.

### `[include:file]: <target>`

* `<target>` - File path.

Includes the `<target>` file.

### `[include:package-list]: <target>`

* `<target>` - Directory path.

Generates package list from `<target>` directory. The readme file will be
used to determine package name and summary.

### `[include:template]: <target> <parameters>`

* `<target>` - File path.
* `<parameters>` - additional parameters
  * `data` - Array of variables (`${name}`) to replace (required).

Includes the `<target>` as a template.

## Limitations

* `<instruction>` will be processed everywhere in the file (eg within
  the code block) and may give unpredictable results.
* `<instruction>` cannot be inside text.
* Nested `<instruction>` doesn't support.

## Arguments

### `path?`

Directory to process.
