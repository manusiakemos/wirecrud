@foreach ($fields as $field)
    @switch($field['type'])
        @case('boolean')
            <wirex-toggle lg
                          wire:key="{{ $field['column'] }}"
                          left-label="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('file')
            <wirex-inputs.filepond
                    wire:key="{{ $field['column'] }}"
                    id="{{ $field['column'] }}" wire:model.live="myFile"/>
            @break
        @case('date')
            <wirex-datetime-picker
                    wire:key="{{ $field['column'] }}"
                    time-format="24" :without-timezone="true" :without-time="true" label="{{ $field['label'] }}"
                    placeholder="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('datetime')
            <wirex-datetime-picker
                    wire:key="{{ $field['column'] }}"
                    time-format="24" :without-timezone="true" :without-time="true" label="{{ $field['label'] }}"
                    placeholder="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('time')
            <wirex-datetime-picker time-format="24" :without-timezone="true" :without-time="false"
                                   label="{{ $field['label'] }}"
                                   placeholder="{{ $field['label'] }}"
                                   wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('longtext')
            <wirex-inputs.editor wire:key="{{ $field['column'] }}" id="{{ $field['column'] }}"
                                 wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('select')
            <wirex-select label="{{ $field['label'] }}"
                          placeholder="Pilih salah satu"
                          wire:key="{{ $field['column'] }}"
                          :options="{{ __('$options') }}['{{ $field['column'] }}']"
                          option-label="{{ $field['label_column'] }}"
                          option-value="{{ $field['column'] }}"
                          wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('string')
            <wirex-input label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                         wire:key="{{ $field['column'] }}"
                         wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('text')
            <wirex-textarea label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                            wire:key="{{ $field['column'] }}"
                            wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('integer')
            <wirex-input type="number" step="1" label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                         wire:key="{{ $field['column'] }}"
                         wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
        @case('double' || 'float')
            <wirex-input label="{{ $field['label'] }}" placeholder="{{ $field['label'] }}"
                         wire:key="{{ $field['column'] }}"
                         wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('radio')
            <wirex-radio
                    wire:key="{{ $field['column'] }}"
                    label="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break

        @case('tinyint')
            <wirex-toggle lg
                          wire:key="{{ $field['column'] }}"
                          left-label="{{ $field['label'] }}" wire:model="{{ $model }}.{{ $field['column'] }}"/>
            @break
    @endswitch
@endforeach
