(function ($) {
    'use strict';

    var $modal = $('#property-detail-modal');
    var $body = $('#property-detail-modal-body');
    var $loading = $body.find('.js-property-modal-loading');
    var activeRequest = null;

    function openModal() {
        $modal.removeClass('hidden');
        $('body').addClass('overflow-hidden');
    }

    function closeModal() {
        if (activeRequest) {
            activeRequest.abort();
            activeRequest = null;
        }
        $modal.addClass('hidden');
        $('body').removeClass('overflow-hidden');
        $body.children().not('.js-property-modal-loading').remove();
        $loading.addClass('hidden');
    }

    function showLoading() {
        $body.children().not('.js-property-modal-loading').remove();
        $loading.removeClass('hidden');
    }

    function loadPropertyDetail(propertyId) {
        showLoading();
        openModal();

        activeRequest = $.ajax({
            url: '/admin/properties/' + propertyId,
            method: 'GET',
            dataType: 'html'
        })
            .done(function (html) {
                $loading.addClass('hidden');
                $body.append(html);
            })
            .fail(function (xhr) {
                $loading.addClass('hidden');
                var message = xhr.status === 404
                    ? 'Property not found.'
                    : 'Unable to load property details. Please try again.';
                $body.append(
                    '<p class="px-4 py-8 text-sm text-red-600 text-center" role="alert">' +
                    $('<span>').text(message).html() +
                    '</p>'
                );
            })
            .always(function () {
                activeRequest = null;
            });
    }

    $(function () {
        $('.js-delete-form').on('submit', function (e) {
            if (!confirm('Delete this property? This cannot be undone.')) {
                e.preventDefault();
            }
        });

        $(document).on('click', '.js-property-detail-trigger', function (e) {
            e.preventDefault();
            var propertyId = $(this).data('property-id');
            if (!propertyId) {
                return;
            }
            loadPropertyDetail(propertyId);
        });

        $modal.on('click', '.js-property-modal-close, .js-property-modal-backdrop', closeModal);

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && !$modal.hasClass('hidden')) {
                closeModal();
            }
        });
    });
}(jQuery));
