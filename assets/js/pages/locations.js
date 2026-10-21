/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.5.0
 * ---------------------------------------------------------------------------- */

/**
 * Locations page.
 *
 * This module implements the functionality of the locations page.
 */
App.Pages.Locations = (function () {
    const $locations = $('#locations');
    const $filterLocations = $('#filter-locations');
    const $id = $('#id');
    const $name = $('#name');
    const $address = $('#address');
    const $city = $('#city');
    const $state = $('#state');
    const $zip = $('#zip');
    let filterResults = {};
    let filterLimit = 20;

    /**
     * Add the page event listeners.
     */
    function addEventListeners() {
        /**
         * Event: Filter Locations Form "Submit"
         *
         * @param {jQuery.Event} event
         */
        $locations.on('submit', '#filter-locations form', (event) => {
            event.preventDefault();
            const key = $('#filter-locations .key').val();
            $('.selected').removeClass('selected');
            App.Pages.Locations.resetForm();
            App.Pages.Locations.filter(key);
        });

        /**
         * Event: Filter Locations Row "Click"
         *
         * Displays the selected row data on the right side of the page.
         *
         * @param {jQuery.Event} event
         */
        $locations.on('click', '.location-row', (event) => {
            if ($('#filter-locations .filter').prop('disabled')) {
                $('#filter-locations .results').css('color', '#AAA');
                return; // exit because we are on edit mode
            }

            const locationId = $(event.currentTarget).attr('data-id');

            const location = filterResults.find(
                (filterResult) => Number(filterResult.id) === Number(locationId),
            );

            App.Pages.Locations.display(location);
            $('#filter-locations .selected').removeClass('selected');
            $(event.currentTarget).addClass('selected');
            $('#edit-location, #delete-location').prop('disabled', false);

            // Automatically enter edit mode
            $('#locations-page').addClass('editing');
            $locations.find('.add-edit-delete-group').hide();
            $locations.find('.save-cancel-group').show();
            $locations.find('#delete-location').show(); // Show delete button when editing
            $locations.find('.record-details').find('input, select, textarea').prop('disabled', false);
            $locations.find('.record-details .form-label span').prop('hidden', false);
            $filterLocations.find('button').prop('disabled', true);
            $filterLocations.find('.results').css('color', '#AAA');
        });

        /**
         * Event: Add Location Button "Click"
         */
        $locations.on('click', '#add-location', () => {
            App.Pages.Locations.resetForm();
            $('#locations-page').addClass('editing');
            $locations.find('.add-edit-delete-group').hide();
            $locations.find('.save-cancel-group').show();
            $locations.find('#delete-location').hide(); // Hide delete button when adding
            $locations.find('.record-details').find('input, select, textarea').prop('disabled', false);
            $locations.find('.record-details .form-label span').prop('hidden', false);
            $filterLocations.find('button').prop('disabled', true);
            $filterLocations.find('.results').css('color', '#AAA');
        });

        /**
         * Event: Edit Location Button "Click"
         */
        $locations.on('click', '#edit-location', () => {
            $('#locations-page').addClass('editing');
            $locations.find('.add-edit-delete-group').hide();
            $locations.find('.save-cancel-group').show();
            $locations.find('.record-details').find('input, select, textarea').prop('disabled', false);
            $locations.find('.record-details .form-label span').prop('hidden', false);
            $filterLocations.find('button').prop('disabled', true);
            $filterLocations.find('.results').css('color', '#AAA');
        });

        /**
         * Event: Delete Location Button "Click"
         */
        $locations.on('click', '#delete-location', () => {
            const locationId = $id.val();

            const buttons = [
                {
                    text: lang('cancel'),
                    click: (event, messageModal) => {
                        messageModal.hide();
                    },
                },
                {
                    text: lang('delete'),
                    click: (event, messageModal) => {
                        remove(locationId);
                        messageModal.hide();
                    },
                },
            ];

            App.Utils.Message.show(lang('delete_location'), lang('delete_record_prompt'), buttons);
        });

        /**
         * Event: Location Save Button "Click"
         */
        $locations.on('click', '#save-location', () => {
            const location = {
                name: $name.val(),
                address: $address.val(),
                city: $city.val(),
                state: $state.val(),
                zip: $zip.val(),
            };

            if ($id.val() !== '') {
                location.id = $id.val();
            }

            if (!validate()) {
                return;
            }

            App.Pages.Locations.save(location);
        });

        /**
         * Event: Cancel Location Button "Click"
         */
        $locations.on('click', '#cancel-location', () => {
            const id = $id.val();
            App.Pages.Locations.resetForm();
            $('#locations-page').removeClass('editing');
            if (id !== '') {
                select(id, true);
            }
        });
    }

    /**
     * Filter locations records.
     *
     * @param {String} keyword This key string is used to filter the location records.
     * @param {Number} selectId Optional, if set then after the filter operation the record with the given
     * ID will be selected (but not displayed).
     * @param {Boolean} show Optional (false), if true then the selected record will be displayed on the form.
     */
    function filter(keyword, selectId = null, show = false) {
        App.Http.Locations.search(keyword, filterLimit).then((response) => {
            filterResults = response;

            $('#filter-locations .results').empty();

            response.forEach((location) => {
                $('#filter-locations .results')
                    .append(App.Pages.Locations.getFilterHtml(location))
                    .append($('<hr/>'));
            });

            if (response.length === 0) {
                $('#filter-locations .results').append(
                    $('<em/>', {
                        'text': lang('no_records_found'),
                    }),
                );
            } else if (response.length === filterLimit) {
                $('<button/>', {
                    'type': 'button',
                    'class': 'btn btn-outline-secondary w-100 load-more text-center',
                    'text': lang('load_more'),
                    'click': () => {
                        filterLimit += 20;
                        App.Pages.Locations.filter(keyword, selectId, show);
                    },
                }).appendTo('#filter-locations .results');
            }

            if (selectId) {
                select(selectId, show);
            }
        });
    }

    /**
     * Save a location record to the database (via AJAX post).
     *
     * @param {Object} location Contains the location data.
     */
    function save(location) {
        App.Http.Locations.save(location).then((response) => {
            App.Layouts.Backend.displayNotification(lang('location_saved'));
            App.Pages.Locations.resetForm();
            $('#locations-page').removeClass('editing');
            $filterLocations.find('.key').val('');
            App.Pages.Locations.filter('', response.id, true);
        });
    }

    /**
     * Delete location record.
     *
     * @param {Number} id Record ID to be deleted.
     */
    function remove(id) {
        App.Http.Locations.destroy(id).then(() => {
            App.Layouts.Backend.displayNotification(lang('location_deleted'));
            App.Pages.Locations.resetForm();
            $('#locations-page').removeClass('editing');
            App.Pages.Locations.filter($('#filter-locations .key').val());
        });
    }

    /**
     * Display a location record on the form.
     *
     * @param {Object} location Contains the location data.
     */
    function display(location) {
        $id.val(location.id);
        $name.val(location.name);
        $address.val(location.address);
        $city.val(location.city);
        $state.val(location.state);
        $zip.val(location.zip);
    }

    /**
     * Validate location data before save (insert or update).
     *
     * @return {Boolean} Returns the validation result.
     */
    function validate() {
        $locations.find('.is-invalid').removeClass('is-invalid');
        $locations.find('.form-message').removeClass('alert-danger').hide();

        try {
            let missingRequired = false;

            $locations.find('.required').each((index, fieldEl) => {
                if (!$(fieldEl).val()) {
                    $(fieldEl).addClass('is-invalid');
                    missingRequired = true;
                }
            });

            if (missingRequired) {
                throw new Error(lang('fields_are_required'));
            }

            return true;
        } catch (error) {
            $locations.find('.form-message').addClass('alert-danger').text(error.message).show();
            return false;
        }
    }

    /**
     * Bring the location form back to its initial state.
     */
    function resetForm() {
        $filterLocations.find('.selected').removeClass('selected');
        $filterLocations.find('button').prop('disabled', false);
        $filterLocations.find('.results').css('color', '');

        $locations.find('.add-edit-delete-group').show();
        $locations.find('.save-cancel-group').hide();
        $locations.find('.record-details').find('input, select, textarea').val('').prop('disabled', true);
        $locations.find('.record-details .form-label span').prop('hidden', true);
        $('#edit-location, #delete-location').prop('disabled', true);

        $locations.find('.record-details .is-invalid').removeClass('is-invalid');
        $locations.find('.record-details .form-message').hide();
    }

    /**
     * Get the filter results row HTML code.
     *
     * @param {Object} location Contains the location data.
     *
     * @return {String} Returns the record HTML code.
     */
    function getFilterHtml(location) {
        return $('<div/>', {
            'class': 'location-row entry',
            'data-id': location.id,
            'html': [
                $('<strong/>', {
                    'text': location.name,
                }),
                $('<br/>'),
            ],
        });
    }

    /**
     * Select a specific record from the current filter results.
     *
     * If the location ID does not exist in the list then no record will be selected.
     *
     * @param {Number} id The record ID to be selected from the filter results.
     * @param {Boolean} show Optional (false), if true then the method will display the record on the form.
     */
    function select(id, show = false) {
        $filterLocations.find('.selected').removeClass('selected');

        $filterLocations.find('.service-category-row[data-id="' + id + '"]').addClass('selected');

        if (show) {
            const location = filterResults.find((location) => Number(location.id) === Number(id));

            App.Pages.Locations.display(location);

            $('#edit-location, #delete-location').prop('disabled', false);
        }
    }

    /**
     * Initialize the module.
     */
    function initialize() {
        App.Pages.Locations.resetForm();
        App.Pages.Locations.filter('');
        App.Pages.Locations.addEventListeners();
    }

    document.addEventListener('DOMContentLoaded', initialize);

    return {
        filter,
        save,
        remove,
        validate,
        getFilterHtml,
        resetForm,
        display,
        select,
        addEventListeners,
    };
})();
