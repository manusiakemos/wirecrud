@foreach ($fields as $field)
    * @OA\Property(property="{{ $field['column'] }}",type="{{ $field['type'] }}"),
@endforeach
