<?php extend('layouts/backend_layout'); ?>

<?php section('content'); ?>

<div class="container backend-page py-3" id="locations-page">

    <div class="row" id="locations">
        <div id="filter-locations" class="filter-records column col-12 mb-4">
            <button id="add-location" class="btn btn-primary add-record-btn mb-4">
                <i class="fas fa-plus-square me-2"></i>
                <?= lang('add') ?>
            </button>

            <form class="input-append mb-4">
                <div class="input-group">
                    <input type="text" class="key form-control" aria-label="keyword">

                    <button class="filter btn btn-outline-secondary" type="submit"
                            data-tippy-content="<?= lang('filter') ?>">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <h4 class="mb-3 fw-light">
                <?= lang('locations') ?>
            </h4>

            <div class="results overflow-auto" style="max-height: 650px;">
                <!-- JS -->
            </div>
        </div>

        <div class="record-details col-12 mb-4">
            <div class="btn-toolbar mb-4">
                <div class="add-edit-delete-group btn-group">
                    <button id="edit-location" class="btn btn-outline-secondary" disabled="disabled">
                        <i class="fas fa-edit me-2"></i>
                        <?= lang('edit') ?>
                    </button>
                </div>

                <div class="save-cancel-group" style="display:none;">
                    <button id="save-location" class="btn btn-primary">
                        <i class="fas fa-check-square me-2"></i>
                        <?= lang('save') ?>
                    </button>
                    <button id="cancel-location" class="btn btn-outline-secondary">
                        <?= lang('cancel') ?>
                    </button>
                    <button id="delete-location" class="btn btn-outline-danger ms-2">
                        <i class="fas fa-trash-alt me-2"></i>
                        <?= lang('delete') ?>
                    </button>
                </div>

            </div>

            <h4 class="mb-3 fw-light">
                <?= lang('details') ?>
            </h4>

            <div class="form-message alert" style="display:none;"></div>

            <input type="hidden" id="id">

            <div class="mb-3">
                <label class="form-label" for="name">
                    <?= lang('name') ?>
                    <span class="text-danger" hidden>*</span>
                </label>
                <input id="name" class="form-control required" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label" for="address">
                    <?= lang('address') ?>
                </label>
                <input id="address" class="form-control" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label" for="city">
                    <?= lang('city') ?>
                </label>
                <input id="city" class="form-control" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label" for="state">
                    <?= lang('state') ?>
                </label>
                <input id="state" class="form-control" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label" for="zip">
                    <?= lang('zip_code') ?>
                </label>
                <input id="zip" class="form-control" disabled>
            </div>

        </div>
    </div>

</div>

<?php end_section('content'); ?>

<?php section('scripts'); ?>

<script src="<?= asset_url('assets/js/http/locations_http_client.js') ?>"></script>
<script src="<?= asset_url('assets/js/pages/locations.js') ?>"></script>

<?php end_section('scripts'); ?>
