# Simple

<!-- markdownlint-disable -->

[simple:a]: https://example.com/

[simple:b]: https://example.com/ "example.com"
[simple:c]: <https://example.com/>
[simple:d]: file/b 'title'

[simple:e]: file/b
[simple:e]: file/b

# Multiline

[multiline:a]: https://example.com/ "
1
2
3
"

[multiline:b]:
    https://example.com/
    (
        example.com
    )

# Inside Quote

> [quote:a]: https://example.com/ (example.com)
>
> [quote:b]:
> https://example.com/

> > [quote:c]: https://example.com/ (example.com)
> >
> > [quote:d]:
> > https://example.com/
> > "example.com"
