# Generated[^1]

[//]: # (start: block)

Text text text text text text text text text text text text text
text text text text text text text text text text text text text
text text text text text text text text text text text text.

[//]: # (start: nested)

Nested should be ignored.

[//]: # (end: nested)

[//]: # (end: block)

> Quote
> [//]: # (start: quote)
> should work
> [//]: # (end: quote)

# References

[simple]: https://example.com/

[multiline]:
https://example.com/
(
    example.com
)

> Quote
>
> ### Quote Heading[^1]
>
> [quote]:
> https://example.com/

# Tables

| Header                   | Header ([link](https://example.com/))                       |
|--------------------------|-------------------------------------------------------------|
| Cell [link][quote] cell. | Cell                                                        |
| Cell                     | Cell cell [link](https://example.com/) cell [link][simple]. |

> Quote
>
> | Header                   |
> |--------------------------|
> | Cell [link][quote] cell. |

# Footnotes

[^1]: Footnote text text text text
