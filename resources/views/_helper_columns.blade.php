@foreach($fields as $column)
@if($column['type'] == 'boolean')
    BooleanColumn::make('{{$column['label']}}', '{{$column['column']}}')
    ->searchable()
    ->sortable(),
@else
    Column::make('{{$column['label']}}', '{{$column['column']}}')
    ->searchable()
    ->sortable(),
@endif
@endforeach
