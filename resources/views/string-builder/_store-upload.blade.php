$filename = $this->uploadFile('{{$classNameSlug}}', $this->myFile, null, 'local');
$data['{{$classNameSnake}}']['{{$uploadColumn['column']}}'] = $filename;