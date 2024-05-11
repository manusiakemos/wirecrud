@foreach($fields as $field)
    @if($field['type'] == 'file' || $field['type'] == 'image')
        if($this->myFile){
            $filename = Str::random().".".$this->myFile->getClientOriginalExtension();
            $this->myFile->storeAs('uploads/{{$classNameLower}}', $filename, 'public');
            $db->{{$field['column']}} = $filename;
        }
    @else
        $db->{{$field['column']}} = $this->{{$classNameLower}}['{{$field['column']}}'];
    @endif
@endforeach
