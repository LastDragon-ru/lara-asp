<?php
/**
 * @var list<array{path: string, title: string, summary: ?string, upgrade: ?string}> $packages
 */

?>
@foreach ($packages as $package)
@if ($package['upgrade'])
* [{!! $package['title'] !!}](<{{ $package['upgrade'] }}>)
@endif
@endforeach
