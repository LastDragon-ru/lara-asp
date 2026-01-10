<?php
/**
 * @var list<array{path: string, title: string, summary: ?string, upgrade: ?string}> $packages
 */

?>
@foreach ($packages as $package)
## {!! $package['title'] !!}
@if($package['summary'])

{!! $package['summary'] !!}
@endif

[Read more](<{{ $package['path'] }}>).
@if (!$loop->last)

@endif
@endforeach
