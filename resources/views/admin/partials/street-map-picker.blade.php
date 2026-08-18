@php
    $latName = $latName ?? 'lat';
    $lngName = $lngName ?? 'lng';
    $latId = $latId ?? $latName;
    $lngId = $lngId ?? $lngName;
    $latValue = old($latName, $latValue ?? '');
    $lngValue = old($lngName, $lngValue ?? '');
    $pickerId = $pickerId ?? 'jk-osm-picker';
@endphp
<div class="jk-osm-picker" data-jk-osm-picker id="{{ $pickerId }}" data-lat-id="{{ $latId }}" data-lng-id="{{ $lngId }}">
    <label for="{{ $pickerId }}-search">Pick a point on the street map</label>
    <div class="jk-osm-picker__search">
        <input id="{{ $pickerId }}-search" type="search" data-jk-osm-search
               placeholder="Search a place in Tanzania, then click the map"
               autocomplete="off">
        <button type="button" class="btn btn-ghost" data-jk-osm-search-btn>Search</button>
    </div>
    <div class="jk-osm-picker__results" data-jk-osm-results hidden></div>
    <div class="jk-osm-picker__map" data-jk-osm-canvas></div>
    <p class="muted" style="margin:.45rem 0 0;font-size:.85rem">Click the map or drag the pin to set the exact coordinates.</p>
    <div class="form-grid two" style="margin-top:.75rem">
        <div>
            <label for="{{ $latId }}">Latitude</label>
            <input id="{{ $latId }}" type="text" name="{{ $latName }}" inputmode="decimal" placeholder="-3.3869"
                   value="{{ $latValue }}">
        </div>
        <div>
            <label for="{{ $lngId }}">Longitude</label>
            <input id="{{ $lngId }}" type="text" name="{{ $lngName }}" inputmode="decimal" placeholder="36.6830"
                   value="{{ $lngValue }}">
        </div>
    </div>
</div>
@once
    @push('scripts')
        <script src="{{ \App\Support\MapCoordinates::scriptUrl() }}" defer></script>
    @endpush
@endonce
