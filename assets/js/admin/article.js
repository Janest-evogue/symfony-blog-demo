const $ = require('jquery');
import 'bootstrap';

$(function () {
    $('.btn-content').click(function (event) {
        event.preventDefault();

        var href = $(this).attr('href');

        $.get(
            href,
            function (response) {
                var $modal = $('#modal-content');

                $modal.find('.modal-body').html(response);

                $modal.modal('show');
            }
        );
    });

    $('#btn-search').click(function () {
        $('#collapseSearch').collapse('toggle');
    });

});
