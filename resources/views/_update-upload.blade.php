if($this->myFile){
    $filename = $this->uploadFile('{{$classNameSlug}}', $this->myFile, $this->{{$classNameSnake}}['{{$uploadColumn['column']}}'], 'local');
    $data['{{$classNameSnake}}']['{{$uploadColumn['column']}}'] = $filename;
}