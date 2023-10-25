# `JsonString`

Represents [JSON](https://json.org) string.

By default, the type validates the string and throws an error if it is not a valid JSON. If you are sure that the string is valid JSON, you can return instance of [`JsonStringable`](../../src/Scalars/JsonStringable.php) (or [`JsonString`](../../src/Scalars/JsonString.php)) in this case validation will be omitted.

Please note that the scalar doesn't encode/decode value to/from JSON, it just contains a valid JSON string. If you want automatically convert value to/from JSON, you can use the `JSON` type from [`mll-lab/graphql-php-scalars`](https://github.com/mll-lab/graphql-php-scalars) package. If you need something more typesafe, consider using [`Serializer`][pkg:serializer].

[include:file]: ../../../../docs/shared/Links.md
[//]: # (start: c547d87b81d5d2374a87eb96d259e596f8b6f4727b3c63dd1817a792e641887d)
[//]: # (warning: Generated automatically. Do not edit.)

[pkg:serializer]:      https://github.com/LastDragon-ru/lara-asp/tree/main/packages/serializer

[//]: # (end: c547d87b81d5d2374a87eb96d259e596f8b6f4727b3c63dd1817a792e641887d)
