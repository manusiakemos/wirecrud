[
@foreach($field_validate as $field)
    "{{$classNameLower}}.{{$field['column']}}" => [
        "required"
    ],
@endforeach
@if($hasUpload)
    "myFile" => ['required','max:2000'],
@endif
]