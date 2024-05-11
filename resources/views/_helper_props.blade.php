[
@foreach($fields as $column)
    @if($column['type'] == 'boolean' || $column['type'] == 'tinyint')
        "{{ $column['column'] }}" => true,
    @else
        "{{ $column['column'] }}" => null,
    @endif
@endforeach
]
