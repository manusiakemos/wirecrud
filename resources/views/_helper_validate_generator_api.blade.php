[
@foreach($field_validate as $field)
    "{{$field['column']}}" => [
        "required"
    ],
@endforeach
]