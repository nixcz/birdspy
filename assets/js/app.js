/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// CSS files
import '../css/main.scss';

// jQuery
import $ from 'jquery';

// Bootstrap
import 'bootstrap';

// Bootstrap Select
import 'bootstrap-select';

// FontAwesome
import '@fortawesome/fontawesome-free/js/all';

// Custom
$(() => {

    // POPOVER
    $('[data-toggle="popover"]').popover({
        html: true
    });

    // TOOLTIP
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body',
        html: true
    });

    // BOOTSTRAP-SELECT
    $('.selectpicker').selectpicker();

});
