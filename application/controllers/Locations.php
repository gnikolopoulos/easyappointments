<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Locations controller.
 *
 * Handles the locations related operations.
 *
 * @package Controllers
 */
class Locations extends EA_Controller
{
    public array $allowed_location_fields = ['id', 'name', 'address', 'city', 'state', 'zip'];

    public array $optional_location_fields = [];

    /**
     * Service-categories constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('locations_model');
        $this->load->model('roles_model');

        $this->load->library('accounts');
        $this->load->library('timezones');
        $this->load->library('webhooks_client');
    }

    /**
     * Render the backend locations page.
     *
     * On this page admin users will be able to manage locations, which are eventually selected by customers during the booking process.
     */
    public function index(): void
    {
        method('get');

        session(['dest_url' => site_url('locations')]);

        $user_id = session('user_id');

        if (cannot('view', PRIV_SERVICES)) {
            if ($user_id) {
                abort(403, 'Forbidden');
            }

            redirect('login');

            return;
        }

        $role_slug = session('role_slug');

        script_vars([
            'user_id' => $user_id,
            'role_slug' => $role_slug,
        ]);

        html_vars([
            'page_title' => lang('locations'),
            'active_menu' => PRIV_SERVICES,
            'user_display_name' => $this->accounts->get_user_display_name($user_id),
            'timezones' => $this->timezones->to_array(),
            'privileges' => $this->roles_model->get_permissions_by_slug($role_slug),
        ]);

        $this->load->view('pages/locations');
    }

    /**
     * Filter locations by the provided keyword.
     */
    public function search(): void
    {
        try {
            method('post');

            if (cannot('view', PRIV_SERVICES)) {
                abort(403, 'Forbidden');
            }

            check('keyword', 'string|null');
            check('order_by', 'string|null');
            check('limit', 'numeric|null');
            check('offset', 'numeric|null');

            $keyword = request('keyword', '');

            $order_by = request('order_by', 'update_datetime DESC');

            $limit = max(1, (int) request('limit', 1000));

            $offset = max(0, (int) request('offset', '0'));

            $locations = $this->locations_model->search($keyword, $limit, $offset, $order_by);

            json_response($locations);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Store a new location.
     */
    public function store(): void
    {
        try {
            method('post');

            if (cannot('add', PRIV_SERVICES)) {
                abort(403, 'Forbidden');
            }

            check('location', 'array');

            $location = request('location');

            $this->locations_model->only($location, $this->allowed_location_fields);

            $this->locations_model->optional($location, $this->optional_location_fields);

            $location_id = $this->locations_model->save($location);

            $location = $this->locations_model->find($location_id);

            $this->webhooks_client->trigger(WEBHOOK_LOCATION_SAVE, $location);

            json_response([
                'success' => true,
                'id' => $location_id,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Find a location.
     */
    public function find(): void
    {
        try {
            method('get');

            if (cannot('view', PRIV_SERVICES)) {
                abort(403, 'Forbidden');
            }

            check('location_id', 'numeric');

            $location_id = request('location_id');

            // Validate location_id is a positive integer
            if (
                empty($location_id) ||
                !filter_var($location_id, FILTER_VALIDATE_INT) ||
                $location_id <= 0
            ) {
                throw new InvalidArgumentException('Invalid location ID provided.');
            }

            $location = $this->locations_model->find($location_id);

            json_response($location);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Update a service-category.
     */
    public function update(): void
    {
        try {
            method('post');

            if (cannot('edit', PRIV_SERVICES)) {
                abort(403, 'Forbidden');
            }

            check('location', 'array');

            $location = request('location');

            $this->locations_model->only($location, $this->allowed_location_fields);

            $this->locations_model->optional($location, $this->optional_location_fields);

            $location_id = $this->locations_model->save($location);

            $location = $this->locations_model->find($location_id);

            $this->webhooks_client->trigger(WEBHOOK_LOCATION_SAVE, $location);

            json_response([
                'success' => true,
                'id' => $location_id,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Remove a service-category.
     */
    public function destroy(): void
    {
        try {
            method('post');

            if (cannot('delete', PRIV_SERVICES)) {
                abort(403, 'Forbidden');
            }

            check('location_id', 'numeric');

            $location_id = request('location_id');

            // Validate location_id is a positive integer
            if (
                empty($location_id) ||
                !filter_var($location_id, FILTER_VALIDATE_INT) ||
                $location_id <= 0
            ) {
                throw new InvalidArgumentException('Invalid location ID provided.');
            }

            $location = $this->locations_model->find($location_id);

            $this->locations_model->delete($location_id);

            $this->webhooks_client->trigger(WEBHOOK_LOCATION_DELETE, $location);

            json_response([
                'success' => true,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }
}
