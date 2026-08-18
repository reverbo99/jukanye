(function () {
    var LEAFLET_CSS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    var LEAFLET_JS = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    var TILE_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    var ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
    var DEFAULT_LAT = -3.3869;
    var DEFAULT_LNG = 36.6830;
    var DEFAULT_ZOOM = 14;
    var leafletLoading = null;

    function loadLeaflet() {
        if (window.L) {
            return Promise.resolve(window.L);
        }
        if (leafletLoading) {
            return leafletLoading;
        }
        leafletLoading = new Promise(function (resolve, reject) {
            if (!document.querySelector('link[data-jk-leaflet]')) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = LEAFLET_CSS;
                link.setAttribute('data-jk-leaflet', '1');
                document.head.appendChild(link);
            }
            var script = document.createElement('script');
            script.src = LEAFLET_JS;
            script.async = true;
            script.onload = function () {
                resolve(window.L);
            };
            script.onerror = function () {
                reject(new Error('Could not load OpenStreetMap'));
            };
            document.head.appendChild(script);
        });
        return leafletLoading;
    }

    function parseMarkers(el) {
        var raw = el.getAttribute('data-markers') || '[]';
        try {
            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function boundsOrCenter(L, markers) {
        var points = markers
            .map(function (m) {
                var lat = Number(m.lat);
                var lng = Number(m.lng);
                if (!isFinite(lat) || !isFinite(lng)) {
                    return null;
                }
                return [lat, lng];
            })
            .filter(Boolean);

        if (points.length === 0) {
            return { center: [DEFAULT_LAT, DEFAULT_LNG], zoom: DEFAULT_ZOOM };
        }
        if (points.length === 1) {
            return { center: points[0], zoom: 16 };
        }
        return { bounds: L.latLngBounds(points) };
    }

    function addMarkers(L, map, markers) {
        var layer = L.featureGroup();
        markers.forEach(function (m) {
            var lat = Number(m.lat);
            var lng = Number(m.lng);
            if (!isFinite(lat) || !isFinite(lng)) {
                return;
            }
            var marker = L.marker([lat, lng]);
            var html = '';
            if (m.title) {
                html += '<strong>' + escapeHtml(m.title) + '</strong>';
            }
            if (m.popup) {
                html += (html ? '<br>' : '') + escapeHtml(m.popup);
            }
            if (html) {
                marker.bindPopup(html);
            }
            marker.addTo(layer);
        });
        if (layer.getLayers().length) {
            layer.addTo(map);
        }
        return layer;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function mountViewer(el) {
        if (el._jkMap) {
            el._jkMap.invalidateSize();
            return;
        }
        loadLeaflet().then(function (L) {
            if (el._jkMap) {
                el._jkMap.invalidateSize();
                return;
            }
            var markers = parseMarkers(el);
            var map = L.map(el, { scrollWheelZoom: false });
            L.tileLayer(TILE_URL, { attribution: ATTRIBUTION, maxZoom: 19 }).addTo(map);
            addMarkers(L, map, markers);
            var view = boundsOrCenter(L, markers);
            if (view.bounds) {
                map.fitBounds(view.bounds, { padding: [28, 28], maxZoom: 16 });
            } else {
                map.setView(view.center, view.zoom);
            }
            el._jkMap = map;
            setTimeout(function () {
                map.invalidateSize();
            }, 120);
        });
    }

    function readNumber(input) {
        if (!input) {
            return null;
        }
        var value = parseFloat(String(input.value).replace(',', '.'));
        return isFinite(value) ? value : null;
    }

    function mountPicker(root) {
        if (root._jkPickerReady) {
            return;
        }
        root._jkPickerReady = true;
        var mapEl = root.querySelector('[data-jk-osm-canvas]');
        var latInput = document.getElementById(root.getAttribute('data-lat-id') || 'lat');
        var lngInput = document.getElementById(root.getAttribute('data-lng-id') || 'lng');
        var searchInput = root.querySelector('[data-jk-osm-search]');
        var searchBtn = root.querySelector('[data-jk-osm-search-btn]');
        var resultsEl = root.querySelector('[data-jk-osm-results]');
        if (!mapEl || !latInput || !lngInput) {
            return;
        }

        loadLeaflet().then(function (L) {
            var lat = readNumber(latInput);
            var lng = readNumber(lngInput);
            var hasPoint = lat !== null && lng !== null;
            var map = L.map(mapEl, { scrollWheelZoom: true });
            L.tileLayer(TILE_URL, { attribution: ATTRIBUTION, maxZoom: 19 }).addTo(map);
            map.setView(
                hasPoint ? [lat, lng] : [DEFAULT_LAT, DEFAULT_LNG],
                hasPoint ? 16 : DEFAULT_ZOOM
            );

            var marker = null;
            function setPoint(nextLat, nextLng, pan) {
                latInput.value = Number(nextLat).toFixed(6);
                lngInput.value = Number(nextLng).toFixed(6);
                if (!marker) {
                    marker = L.marker([nextLat, nextLng], { draggable: true }).addTo(map);
                    marker.on('dragend', function () {
                        var pos = marker.getLatLng();
                        setPoint(pos.lat, pos.lng, false);
                    });
                } else {
                    marker.setLatLng([nextLat, nextLng]);
                }
                if (pan) {
                    map.setView([nextLat, nextLng], Math.max(map.getZoom(), 16));
                }
            }

            if (hasPoint) {
                setPoint(lat, lng, false);
            }

            map.on('click', function (event) {
                setPoint(event.latlng.lat, event.latlng.lng, false);
            });

            ['change', 'blur'].forEach(function (evt) {
                latInput.addEventListener(evt, syncFromInputs);
                lngInput.addEventListener(evt, syncFromInputs);
            });

            function syncFromInputs() {
                var nextLat = readNumber(latInput);
                var nextLng = readNumber(lngInput);
                if (nextLat === null || nextLng === null) {
                    return;
                }
                setPoint(nextLat, nextLng, true);
            }

            function renderResults(items) {
                if (!resultsEl) {
                    return;
                }
                resultsEl.innerHTML = '';
                if (!items.length) {
                    resultsEl.hidden = true;
                    return;
                }
                items.forEach(function (item) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'jk-osm-picker__result';
                    btn.textContent = item.display_name;
                    btn.addEventListener('click', function () {
                        setPoint(parseFloat(item.lat), parseFloat(item.lon), true);
                        resultsEl.hidden = true;
                        if (searchInput) {
                            searchInput.value = item.display_name;
                        }
                    });
                    resultsEl.appendChild(btn);
                });
                resultsEl.hidden = false;
            }

            function searchPlace() {
                if (!searchInput) {
                    return;
                }
                var query = searchInput.value.trim();
                if (query.length < 2) {
                    return;
                }
                var url =
                    'https://nominatim.openstreetmap.org/search?format=json&limit=6&countrycodes=tz&q=' +
                    encodeURIComponent(query);
                fetch(url, { headers: { Accept: 'application/json' } })
                    .then(function (res) {
                        return res.json();
                    })
                    .then(function (data) {
                        renderResults(Array.isArray(data) ? data : []);
                    })
                    .catch(function () {
                        renderResults([]);
                    });
            }

            if (searchBtn) {
                searchBtn.addEventListener('click', searchPlace);
            }
            if (searchInput) {
                searchInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        searchPlace();
                    }
                });
            }

            setTimeout(function () {
                map.invalidateSize();
            }, 150);
        });
    }

    function boot() {
        document.querySelectorAll('.jk-osm-map').forEach(mountViewer);
        document.querySelectorAll('[data-jk-osm-picker]').forEach(mountPicker);
        document.querySelectorAll('details').forEach(function (details) {
            details.addEventListener('toggle', function () {
                if (!details.open) {
                    return;
                }
                details.querySelectorAll('.jk-osm-map').forEach(function (el) {
                    if (el._jkMap) {
                        setTimeout(function () {
                            el._jkMap.invalidateSize();
                        }, 60);
                    } else {
                        mountViewer(el);
                    }
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
