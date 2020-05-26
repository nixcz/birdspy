/*
 * Community Lookup
 */

import $ from 'jquery';

import './validation';

$(() => {

    $('#community-lookup-form').validate({
        rules: {
            table: {
                required: true
            },
            community: {
                required: true,
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

});
