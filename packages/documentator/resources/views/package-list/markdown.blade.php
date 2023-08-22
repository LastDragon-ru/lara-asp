<?php
/**
 * @var list<array{path: string, title: string, summary: ?string, readme: string}> $packages
 */

?>
@foreach ($packages as $package)
## {{ $package['title'] }}
@if($package['summary'])

{{ $package['summary'] }}
@endif

[Read more](<{{ $package['readme'] }}>).
@if (!$loop->last)

@endif
@endforeach
