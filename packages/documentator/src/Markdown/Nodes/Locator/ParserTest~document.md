# Simple

<!-- markdownlint-disable -->

Text text _**[link](https://example.com/)**_.

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

| Header                  |  Header ([link](https://example.com/))                                    |
|-------------------------|---------------------------------------------------------------------------|
| Cell [link][link] cell. | Cell                                                                      |
| Cell                    | Cell cell [link](https://example.com/) cell [link](https://example.com/). |

> | Header                                           | Header |
> |--------------------------------------------------|--------|
> | Cell `\|` \\| [link](https://example.com/ "\\|") | Cell   |

# Images

Text text ![image](https://example.com/) text ![image](https://example.com/ "title")
text text ![image][link] text text ![image](https://example.com/) text text text
text text text text text text text text text text text text text text text text text
text text _![image](https://example.com/)_ text.

![image](https://example.com/)

![image][link]

> ![image](https://example.com/)

| Header                         | Header |
|--------------------------------|--------|
| ![image](https://example.com/) | Cell   |

# Footnotes

[^1]: Footnote text text text text

[^note]: Footnote text text text text text [link](https://example.com/)[^1] text
    text text text [link](https://example.com/) text text text.

> Text text[^quote]
>
> [^quote]: Footnote text text text text text [link](https://example.com/)
>     text text text text text [link](https://example.com/).
