[
@foreach($field_validate as $field)
   @if ($field['type'] == 'tinyint' || $field['type'] == 'boolean')
    "{{$classNameLower}}.{{$field['column']}}" => [
        "required", "boolean"
    ],
   @else
    "{{$classNameLower}}.{{$field['column']}}" => [
        "required"
    ],
   @endif
@endforeach
@if($hasUpload)
    "myFile" => ['required','max:2000', 'mimes:jpg,jpeg,png', 'extensions:jpg,jpeg,png'],
@endif
]