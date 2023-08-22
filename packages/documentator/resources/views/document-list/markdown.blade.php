<?php
/**
 * @var list<array{path: string, title: string, summary: ?string}> $documents
 */

?>
@foreach ($documents as $document)
## {{ $document['title'] }}
@if($document['summary'])

{{ $document['summary'] }}
@endif

[Read more](<{{ $document['path'] }}>).
@if (!$loop->last)

@endif
@endforeach
