<!-- Generated automatically. Do not edit. -->

# `lara-asp-documentator:preprocess`

Preprocess Markdown files.

## Usages

* `lara-asp-documentator:preprocess [<path>]`

## Description

Replaces special instructions in Markdown.

```plain
[<instruction>]: <target>
[<instruction>=name]: <target>
```

### Supported instructions

#### `[include:document-list]: <target>`

* `<target>` - Directory path.

Returns the list of `*.md` files in the `<target>` directory. Each file
must have `# Header` as the first construction. The first paragraph
after the Header will be used as a summary.

#### `[include:example]: <target>`

* `<target>` - Example file path.

Includes contents of the `<target>` file as an example wrapped into
` ```code block``` `. It also searches for `<target>.run` file, execute
it if found, and include its result right after the code block.

By default, output of `<target>.run` will be included as ` ```plain text``` `
block. You can wrap the output into `<markdown>text</markdown>` tags to
insert it as is.

#### `[include:exec]: <target>`

* `<target>` - Path to the executable.

Executes the `<target>` and returns result.

#### `[include:file]: <target>`

* `<target>` - File path.

Includes the `<target>` file.

#### `[include:package-list]: <target>`

* `<target>` - Directory path.

Generates package list from `<target>` directory. The readme file will be
used to determine package name and summary.

### Limitations

* `<instruction>` will be processed everywhere in the file (eg within
  the code block) and may give unpredictable results.
* `<instruction>` cannot be inside text.
* Nested `<instruction>` doesn't support.

## Arguments

### `path?`

Directory to process.
