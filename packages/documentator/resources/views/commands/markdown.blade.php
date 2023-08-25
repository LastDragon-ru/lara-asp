<?php
/**
 * @var \LastDragon_ru\LaraASP\Documentator\Utils\ArtisanSerializer $serializer
 * @var \Illuminate\Console\Command                                 $command
 */

?>
<!-- Generated automatically. Do not edit. -->

# `{{ $command->getName() }}`
@if($command->getDescription())

{!! $command->getDescription() !!}
@endif

## Usages

@foreach(array_merge([$command->getSynopsis()], $command->getAliases(), $command->getUsages()) as $usage)
* `{!! $usage !!}`
@endforeach
@if($command->getDescription() !== ($help = $command->getProcessedHelp()))

## Description

{!! $help !!}
@endif
@if($command->getDefinition()->getArguments())

## Arguments
@foreach($command->getDefinition()->getArguments() as $argument)

### `{!! $serializer->getArgumentSignature($argument) !!}`

{!! $argument->getDescription() ?: '_No description provided._' !!}
@endforeach
@endif
@if($command->getDefinition()->getOptions())

## Options
@foreach($command->getDefinition()->getOptions() as $option)

### `{!! $serializer->getOptionSignature($option) !!}`@if($option->isNegatable()), `--no-{{ $option->getName() }}`@endif


{!! $option->getDescription() ?: '_No description provided._' !!}
@endforeach
@endif
