@foreach($fields as $column)
@php
$labelnya = Str::replace('_', ' ', $column['label']);
@endphp
@if($column['type'] == 'tinyint' || $column['type'] == 'boolean')
    BooleanColumn::make('{{$labelnya}}', '{{$column['column']}}')
    ->searchable()
    ->sortable(),
@else
    Column::make('{{$labelnya}}', '{{$column['column']}}')
    ->searchable()
    ->sortable(),
@endif
@endforeach
