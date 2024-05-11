$filename = $this->uploadFile('{{$classNameLower}}', $this->myFile);
$data['{{$classNameLower}}']['{{$uploadColumn['column']}}'] = $filename;