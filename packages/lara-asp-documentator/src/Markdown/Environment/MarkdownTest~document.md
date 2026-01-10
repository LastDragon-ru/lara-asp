# Simple[^1]

<!-- markdownlint-disable -->

Text text _**[**_`link`_**](https://example.com/)**_ text [**_`link`](https://example.com/).

Text text [link](https://example.com/)[^1] text [link](https://example.com/ "title") text[^1]
text text text text [link][link] text text [link](https://example.com/) text text text text text text
text text text text text text text text text text[^2] text text text text text text text
text text _[link](https://example.com/)_[^note] text.

[link]: https://example.com/ "reference"

# Lists

* List list [link](https://example.com/).
    * List list [link](https://example.com/).
    * List list [link](https://example.com/ "\\|").

# Quotes

> Quote quote [link](https://example.com/).
>
> Quote quote quote quote quote quote quote quote quote quote quote quote quote quote quote quote quote
> quote quote [link](https://example.com/) quote.

> > Quote quote [link](https://example.com/).

# Tables

| Header                  | Header ([link](https://example.com/))                                     |
|-------------------------|---------------------------------------------------------------------------|
| Cell [link][link] cell. | Cell                                                                      |
| Cell                    | Cell cell [link](https://example.com/) cell [link](https://example.com/). |
| Cell                    | Cell `\|` \\| [table][link].                                              |

> | Header                                           | Header |
> |--------------------------------------------------|--------|
> | Cell `\|` \\| [link](https://example.com/ "\\|") | Cell   |
> | Cell `\|` \\| [table][link].                     | Cell   |

# Images

Text text ![image](https://example.com/) text ![image](https://example.com/ "title")
text text ![image][link] text text ![image](https://example.com/) text text text
text text text text text text text text text text text text text text text text text
text text _![image](https://example.com/)_ text.

![image](https://example.com/)

![image][link]

> ![image](https://example.com/)

| Header                         | Header                      |
|--------------------------------|-----------------------------|
| ![image](https://example.com/) | Cell                        |
| Cell                           | Cell `\|` \\| [table][link] |

# Footnotes

[^1]: Footnote text text text text

[^note]: Footnote text text text text text [link](https://example.com/)[^1] text
text text text [link](https://example.com/) text text text.

> Text text[^quote]
>
> [^quote]: Footnote text text text text text [link](https://example.com/)
>     text text text text text [link](https://example.com/).
>
> [^quote-unused]: Unused inside quote.

[^unused]: Text text text text text text text text text

    Text text text text text text text text text text text text
    text text text text text text text text text text text text.

# Inline code

Text `code` text text **`code`** text text text text `code` text
text `code` `code` _`code`_ [`code`][link] `[code][link]` text.

Text `
code
` text.

> Text `code` text.
>
> > `code`

| Header | `Header`    |
|--------|-------------|
| `code` | Cell `code` |

[link]: https://example.com
