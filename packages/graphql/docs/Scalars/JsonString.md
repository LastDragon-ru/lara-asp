# `JsonString`

Represents [JSON](https://json.org) string.

By default, the type validates the string and throws an error if it is not a valid JSON. If you are sure that the string is valid JSON, you can return instance of [`JsonStringable`](../../src/Scalars/JsonStringable.php) (or [`JsonString`](../../src/Scalars/JsonString.php)) in this case validation will be omitted.

Please note that the scalar doesn't encode/decode value to/from JSON, it just contains a valid JSON string. If you want automatically convert value to/from JSON, you can use the `JSON` type from [`mll-lab/graphql-php-scalars`](https://github.com/mll-lab/graphql-php-scalars) package. If you need something more typesafe, consider using [`Serializer`][pkg:serializer].

[include:file]: ../../../../docs/Shared/Links.md
[//]: # (start: 0cb5b7fc2d139f2e4cbb483b37e572eecba024b637e626cce86816ef0246939f)
[//]: # (warning: Generated automatically. Do not edit.)

[pkg:serializer]:      https://github.com/LastDragon-ru/lara-asp/tree/main/packages/serializer

[//]: # (end: 0cb5b7fc2d139f2e4cbb483b37e572eecba024b637e626cce86816ef0246939f)
