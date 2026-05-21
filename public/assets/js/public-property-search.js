(function ($) {
    'use strict';

    var $input = $('#displayable_address');
    var $list = $('#address-autocomplete-list');
    var debounceTimer = null;
    var activeRequest = null;
    var selectedIndex = -1;

    if ($input.length === 0) {
        return;
    }

    function hideList() {
        $list.addClass('hidden').empty();
        $input.attr('aria-expanded', 'false');
        selectedIndex = -1;
    }

    function showList() {
        $list.removeClass('hidden');
        $input.attr('aria-expanded', 'true');
    }

    function highlightItem(index) {
        var $items = $list.find('[role="option"]');
        if (index < 0 || index >= $items.length) {
            return;
        }
        $items.removeClass('bg-slate-100');
        var $item = $items.eq(index);
        $item.addClass('bg-slate-100');
        $input.val($item.text());
        selectedIndex = index;
    }

    function selectHighlighted() {
        if (selectedIndex < 0) {
            return;
        }
        hideList();
    }

    function renderSuggestions(addresses) {
        $list.empty();
        if (!addresses || addresses.length === 0) {
            hideList();
            return;
        }

        addresses.forEach(function (address, i) {
            var $item = $('<li></li>')
                .attr('role', 'option')
                .attr('id', 'address-option-' + i)
                .addClass('px-3 py-2 cursor-pointer hover:bg-slate-100 text-slate-900')
                .text(address)
                .on('mousedown', function (e) {
                    e.preventDefault();
                    $input.val(address);
                    hideList();
                });
            $list.append($item);
        });

        showList();
        selectedIndex = -1;
    }

    function fetchSuggestions(term) {
        if (activeRequest) {
            activeRequest.abort();
            activeRequest = null;
        }

        activeRequest = $.ajax({
            url: '/properties/addresses/autocomplete',
            method: 'GET',
            data: { q: term },
            dataType: 'json'
        })
            .done(function (data) {
                if (Array.isArray(data)) {
                    renderSuggestions(data);
                } else {
                    hideList();
                }
            })
            .fail(function (xhr) {
                if (xhr.statusText !== 'abort') {
                    hideList();
                }
            })
            .always(function () {
                activeRequest = null;
            });
    }

    function onInputChange() {
        var term = $.trim($input.val());
        if (term.length < 2) {
            hideList();
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchSuggestions(term);
        }, 250);
    }

    $input.on('input', onInputChange);

    $input.on('keydown', function (e) {
        var $items = $list.find('[role="option"]');
        if ($items.length === 0 || $list.hasClass('hidden')) {
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightItem(Math.min(selectedIndex + 1, $items.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightItem(Math.max(selectedIndex - 1, 0));
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            selectHighlighted();
        } else if (e.key === 'Escape') {
            hideList();
        }
    });

    $input.on('blur', function () {
        setTimeout(hideList, 150);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#displayable_address, #address-autocomplete-list').length) {
            hideList();
        }
    });
}(jQuery));
