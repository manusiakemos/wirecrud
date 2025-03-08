public $options = [
    @foreach($fields as $column)
        '{{$column['column']}}' => [],
    @endforeach
];