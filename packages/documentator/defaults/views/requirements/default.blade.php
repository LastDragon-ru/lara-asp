<?php
/**
 * @var array<string, string>                                            $packages Package name (key) and title (value)
 * @var array<string, array<string, list<string|array{string, string}>>> $requirements
 */

?>
# Requirements

@if($requirements)
| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
@foreach ($packages as $key => $title)
@foreach ($requirements[$key] as $constraint => $versions)
| @if ($loop->first) {{$title}} @endif | `{!!$constraint!!}` | @foreach($versions as $version) @if(is_string($version))`{{$version}}` @else `{{$version[0]}} ⋯ {{$version[1]}}` @endif @if (!$loop->last), @endif
@endforeach |
@endforeach
@endforeach
@else
_No requirements._
@endif
