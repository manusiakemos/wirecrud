@foreach ($fields as $field)
    @switch($field['type'])
        @case('file')
            <xxx-inputs.filepond
                    wire:key="{{ $field['column'] }}"
                    id="{{ $field['column'] }}" wire:model.live="myFile"/>
            @break
        @case('date')
            <xxx-datetime-picker
                    wire:key="{{ $field['column'] }}"
                    time-format="24" :without-timezone="true" :without-time="true" label="{{ $field['label'] }}"
                    placeholder="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('datetime')
            <xxx-datetime-picker
                    wire:key="{{ $field['column'] }}"
                    time-format="24" :without-timezone="true" :without-time="true" label="{{ $field['label'] }}"
                    placeholder="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('time')
            <xxx-datetime-picker time-format="24" :without-timezone="true" :without-time="false"
                                 label="{{ $field['label'] }}"
                                 placeholder="{{ $field['label'] }}"
                                 wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('longtext')
            <xxx-inputs.editor wire:key="{{ $field['column'] }}" id="{{ $field['column'] }}"
                               wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('select')
            <xxx-select label="{{ $field['label'] }}"
                        placeholder="Pilih salah satu"
                        wire:key="{{ $field['column'] }}"
                        :options="{{ __('$options') }}['{{ $field['column'] }}']"
                        option-label="{{ $field['label_column'] }}"
                        option-value="{{ $field['column'] }}"
                        wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('string')
            <xxx-input label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                       wire:key="{{ $field['column'] }}"
                       wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('text')
            <xxx-textarea label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                          wire:key="{{ $field['column'] }}"
                          wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('integer')
            <xxx-input type="number" step="1" label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                       wire:key="{{ $field['column'] }}"
                       wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('double' || 'float')
            <xxx-input type="text" label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                       wire:key="{{ $field['column'] }}"
                       wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('radio')
            <xxx--radio
                    wire:key="{{ $field['column'] }}"
                    label="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('tinyint')
            <xxx-toggle lg
                        wire:key="{{ $field['column'] }}"
                        left-label="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('boolean')
            <xxx-toggle lg
                        wire:key="{{ $field['column'] }}"
                        left-label="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
    @endswitch
@endforeach
