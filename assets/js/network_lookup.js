/*
 * Network Lookup
 */

import $ from 'jquery';

import './validation';

$(() => {

    $('#network-lookup-form').validate({
        rules: {
            source: {
                required: true
            },
            network: {
                required: true,
                //network: true // TODO...
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

});
