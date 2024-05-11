@foreach ($fields as $field)
    $db->{{ $field['name'] }} = $request->{{ $field['name'] }};
@endforeach
