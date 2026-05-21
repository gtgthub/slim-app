/* global google, initPropertyLocationMap */
(function ($) {
    'use strict';

    var $form = $('#location-search-form');
    var $mapEl = $('#property-location-map');
    var $pickerEl = $('#location-picker-map');
    var $latInput = $('#latitude');
    var $lngInput = $('#longitude');
    var $locationInput = $('#location');
    var $tabInput = $('#search-tab');
    var $submitBtn = $('#location-search-submit');
    var $mapAutoHint = $('#location-map-auto-hint');

    var resultsMap = null;
    var resultsCenterMarker = null;
    var pickerMap = null;
    var pickerCenterMarker = null;
    var propertyMarkers = [];
    var infoWindow = null;
    var autocomplete = null;
    var pickerReady = false;
    var activeTab = 'text';
    var mapsEnabled = true;

    var TAB_ACTIVE = 'border-slate-800 text-slate-900';
    var TAB_INACTIVE = 'border-transparent text-slate-600 hover:text-slate-900';

    function parseFloatOrNull(value) {
        var n = parseFloat(value);
        return isNaN(n) ? null : n;
    }

    function getDefaultCenter() {
        var lat = parseFloatOrNull($mapEl.data('search-lat'));
        var lng = parseFloatOrNull($mapEl.data('search-lng'));
        if (lat !== null && lng !== null) {
            return { lat: lat, lng: lng };
        }
        if ($pickerEl.length) {
            lat = parseFloatOrNull($pickerEl.data('default-lat'));
            lng = parseFloatOrNull($pickerEl.data('default-lng'));
            if (lat !== null && lng !== null) {
                return { lat: lat, lng: lng };
            }
        }
        return { lat: 53.3498, lng: -6.2603 };
    }

    function syncCenterToForm(position) {
        var lat = typeof position.lat === 'function' ? position.lat() : position.lat;
        var lng = typeof position.lng === 'function' ? position.lng() : position.lng;
        $latInput.val(String(Math.round(lat * 10000000) / 10000000));
        $lngInput.val(String(Math.round(lng * 10000000) / 10000000));
    }

    function createCenterMarkerIcon() {
        return {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: '#1e293b',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2
        };
    }

    function setCenterMarkerOnMap(map, existingMarker, position, draggable) {
        if (existingMarker) {
            existingMarker.setPosition(position);
            return existingMarker;
        }
        return new google.maps.Marker({
            position: position,
            map: map,
            draggable: draggable,
            title: 'Search location',
            icon: createCenterMarkerIcon(),
            zIndex: 1000
        });
    }

    function buildInfoWindowContent(markerData) {
        var container = document.createElement('div');
        container.className = 'text-sm max-w-[220px]';

        var title = document.createElement('p');
        title.className = 'font-medium text-slate-900 mb-1';
        title.textContent = markerData.address || '';
        container.appendChild(title);

        var price = document.createElement('p');
        price.className = 'text-slate-600 mb-2';
        price.textContent = '\u00A3' + (markerData.price || '') + ' \u00B7 ' + (markerData.listing || '');
        container.appendChild(price);

        var link = document.createElement('a');
        link.href = markerData.detailUrl || '#';
        link.className = 'text-slate-800 underline font-medium';
        link.textContent = 'View details';
        container.appendChild(link);

        return container;
    }

    function clearPropertyMarkers() {
        propertyMarkers.forEach(function (m) {
            m.setMap(null);
        });
        propertyMarkers = [];
    }

    function renderPropertyMarkers(markersData) {
        if (!resultsMap || !markersData || !markersData.length) {
            return;
        }

        clearPropertyMarkers();
        var bounds = new google.maps.LatLngBounds();
        var center = resultsCenterMarker ? resultsCenterMarker.getPosition() : resultsMap.getCenter();
        if (center) {
            bounds.extend(center);
        }

        markersData.forEach(function (data) {
            var position = { lat: data.lat, lng: data.lng };
            var marker = new google.maps.Marker({
                position: position,
                map: resultsMap,
                title: data.address || ''
            });
            marker.__propertyId = data.id;
            marker.addListener('click', function () {
                openInfoWindow(marker, data);
                highlightSidebarCard(data.id);
            });
            propertyMarkers.push(marker);
            bounds.extend(position);
        });

        if (markersData.length > 0) {
            resultsMap.fitBounds(bounds, 48);
        }
    }

    function openInfoWindow(marker, markerData) {
        if (!infoWindow) {
            infoWindow = new google.maps.InfoWindow();
        }
        infoWindow.setContent(buildInfoWindowContent(markerData));
        infoWindow.open(resultsMap, marker);
    }

    function highlightSidebarCard(propertyId) {
        $('.location-result-card').removeClass('ring-2 ring-slate-800 border-slate-800');
        var $card = $('.location-result-card[data-property-id="' + propertyId + '"]');
        $card.addClass('ring-2 ring-slate-800 border-slate-800');
        if ($card.length && $card[0].scrollIntoView) {
            $card[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    function findMarkerDataById(propertyId) {
        var markers = window.propertyMapMarkers || [];
        for (var i = 0; i < markers.length; i++) {
            if (Number(markers[i].id) === propertyId) {
                return { marker: propertyMarkers[i], data: markers[i] };
            }
        }
        return null;
    }

    function getFormPosition() {
        var formLat = parseFloatOrNull($latInput.val());
        var formLng = parseFloatOrNull($lngInput.val());
        if (formLat !== null && formLng !== null) {
            return new google.maps.LatLng(formLat, formLng);
        }
        var center = getDefaultCenter();
        return new google.maps.LatLng(center.lat, center.lng);
    }

    function submitMapSearch() {
        if (!pickerReady) {
            return;
        }
        $tabInput.val('map');
        $locationInput.val('');
        $form[0].submit();
    }

    function bindPickerMarkerEvents(marker) {
        google.maps.event.clearListeners(marker, 'dragend');
        marker.addListener('dragend', function () {
            syncCenterToForm(marker.getPosition());
            submitMapSearch();
        });
    }

    function initPlacesAutocomplete() {
        if (!$locationInput.length || typeof google.maps.places === 'undefined') {
            return;
        }

        if (autocomplete) {
            return;
        }

        autocomplete = new google.maps.places.Autocomplete($locationInput[0], {
            fields: ['geometry', 'formatted_address', 'name']
        });

        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                return;
            }
            var loc = place.geometry.location;
            syncCenterToForm(loc);

            if (pickerMap) {
                pickerMap.panTo(loc);
                pickerMap.setZoom(13);
                pickerCenterMarker = setCenterMarkerOnMap(pickerMap, pickerCenterMarker, loc, true);
            }
            if (resultsMap) {
                resultsMap.panTo(loc);
                resultsMap.setZoom(13);
                resultsCenterMarker = setCenterMarkerOnMap(resultsMap, resultsCenterMarker, loc, false);
            }

            if (place.formatted_address) {
                $locationInput.val(place.formatted_address);
            }
        });
    }

    function bindSidebarCards() {
        $(document).on('click keydown', '.location-result-card', function (e) {
            if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') {
                return;
            }
            if (e.type === 'keydown') {
                e.preventDefault();
            }
            if ($(e.target).closest('a').length) {
                return;
            }
            var propertyId = parseInt($(this).data('property-id'), 10);
            var found = findMarkerDataById(propertyId);
            if (found && found.marker && found.data && resultsMap) {
                resultsMap.panTo(found.marker.getPosition());
                resultsMap.setZoom(14);
                openInfoWindow(found.marker, found.data);
                highlightSidebarCard(propertyId);
            }
        });
    }

    function updateTabUi(tab) {
        activeTab = tab;
        $tabInput.val(tab);

        $('.location-search-tab').each(function () {
            var $btn = $(this);
            var isActive = $btn.data('location-tab') === tab;
            $btn.attr('aria-selected', isActive ? 'true' : 'false');
            $btn.removeClass(TAB_ACTIVE + ' ' + TAB_INACTIVE);
            $btn.addClass(isActive ? TAB_ACTIVE : TAB_INACTIVE);
        });

        $('#location-panel-text').toggleClass('hidden', tab !== 'text');
        $('#location-panel-map').toggleClass('hidden', tab !== 'map');
        $submitBtn.toggleClass('hidden', tab !== 'text');
        $mapAutoHint.toggleClass('hidden', tab !== 'map');

        if (tab === 'text') {
            initPlacesAutocomplete();
        }
    }

    function bindTabControls() {
        $submitBtn.on('click', function () {
            $tabInput.val('text');
        });

        $('.location-search-tab').on('click', function () {
            var $btn = $(this);
            if ($btn.prop('disabled')) {
                return;
            }
            var tab = $btn.data('location-tab');
            updateTabUi(tab);

            if (tab === 'map' && pickerMap) {
                google.maps.event.trigger(pickerMap, 'resize');
                var pos = getFormPosition();
                pickerMap.setCenter(pos);
                if (pickerCenterMarker) {
                    pickerCenterMarker.setPosition(pos);
                }
            }
        });
    }

    function initPickerMap() {
        if ($pickerEl.length === 0 || typeof google === 'undefined') {
            return;
        }

        pickerReady = false;
        var center = getDefaultCenter();
        var initialPosition = getFormPosition();

        pickerMap = new google.maps.Map($pickerEl[0], {
            center: center,
            zoom: 8,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });

        pickerCenterMarker = setCenterMarkerOnMap(pickerMap, null, initialPosition, true);
        bindPickerMarkerEvents(pickerCenterMarker);

        pickerMap.addListener('click', function (event) {
            var hadMarker = pickerCenterMarker !== null;
            pickerCenterMarker = setCenterMarkerOnMap(pickerMap, pickerCenterMarker, event.latLng, true);
            if (!hadMarker) {
                bindPickerMarkerEvents(pickerCenterMarker);
            }
            syncCenterToForm(event.latLng);
            submitMapSearch();
        });

        if (activeTab === 'map') {
            google.maps.event.trigger(pickerMap, 'resize');
            pickerMap.setCenter(initialPosition);
        }

        window.setTimeout(function () {
            pickerReady = true;
        }, 0);
    }

    function initResultsMap() {
        if ($mapEl.length === 0 || typeof google === 'undefined') {
            return;
        }

        var center = getDefaultCenter();
        var hasSearch = $mapEl.data('has-search') === 1 || $mapEl.data('has-search') === '1';
        var initialPosition = getFormPosition();

        resultsMap = new google.maps.Map($mapEl[0], {
            center: center,
            zoom: hasSearch ? 12 : 8,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });

        infoWindow = new google.maps.InfoWindow();
        resultsCenterMarker = setCenterMarkerOnMap(resultsMap, null, initialPosition, false);

        renderPropertyMarkers(window.propertyMapMarkers || []);
    }

    window.initPropertyLocationMap = function () {
        if ($form.length === 0) {
            return;
        }

        mapsEnabled = $form.data('maps-enabled') === 1 || $form.data('maps-enabled') === '1';
        activeTab = $form.data('active-tab') === 'map' ? 'map' : 'text';

        bindTabControls();
        bindSidebarCards();
        initResultsMap();
        initPickerMap();

        if (activeTab === 'text') {
            initPlacesAutocomplete();
        }
    };

    if ($form.length === 0) {
        return;
    }

    // If Maps API failed to load (no API key), callback never runs — map areas stay blank.
}(jQuery));
