@props([
    'id',
    'title' => '',
    'size' => '', // sm, lg, xl, fullscreen
    'scrollable' => true,
    'centered' => true,
    'static' => false
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" 
     @if($static) data-bs-backdrop="static" data-bs-keyboard="false" @endif>
    <div class="modal-dialog {{ $size ? 'modal-'.$size : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }} {{ $centered ? 'modal-dialog-centered' : '' }}">
        <div class="modal-content">
            @if($title || isset($header))
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title ?? $header }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="modal-body">
                {{ $slot }}
            </div>
            
            @if(isset($footer))
                <div class="modal-header border-top">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
