<?php
/**
 * @var \LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Data $data
 */

?>
@foreach ($data->documents as $document)
{{ str_repeat('#', $data->level) }} {!! $document->title !!}
@if($document->summary)

{!! $document->summary !!}
@endif

[Read more](<{{ $document->path }}>).
@if (!$loop->last)

@endif
@endforeach
