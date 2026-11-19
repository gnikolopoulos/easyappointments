<?php defined('BASEPATH') or exit('No direct script access allowed');

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
 * Locations API v1 controller.
 *
 * @package Controllers
 */
class Locations_api_v1 extends EA_Controller
{
    /**
     * Locations_api_v1 constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->library('api');
        $this->load->library('webhooks_client');

        $this->api->auth();

        $this->api->model('locations_model');
    }

    /**
     * Get a location collection.
     */
    public function index(): void
    {
        try {
            $keyword = $this->api->request_keyword();

            $limit = $this->api->request_limit();

            $offset = $this->api->request_offset();

            $order_by = $this->api->request_order_by();

            $fields = $this->api->request_fields();

            $with = $this->api->request_with();

            $locations = empty($keyword)
                ? $this->locations_model->get(null, $limit, $offset, $order_by)
                : $this->locations_model->search($keyword, $limit, $offset, $order_by);

            foreach ($locations as &$location) {
                $this->locations_model->api_encode($location);

                if (!empty($fields)) {
                    $this->locations_model->only($location, $fields);
                }

                if (!empty($with)) {
                    $this->locations_model->load($location, $with);
                }
            }

            json_response($locations);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Get a single location.
     *
     * @param int|null $id Location ID.
     */
    public function show(?int $id = null): void
    {
        try {
            $occurrences = $this->locations_model->get(['id' => $id]);

            if (empty($occurrences)) {
                response('', 404);

                return;
            }

            $fields = $this->api->request_fields();

            $with = $this->api->request_with();

            $location = $this->locations_model->find($id);

            $this->locations_model->api_encode($location);

            if (!empty($fields)) {
                $this->locations_model->only($location, $fields);
            }

            if (!empty($with)) {
                $this->locations_model->load($location, $with);
            }

            json_response($location);
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
            $location = request();

            $this->locations_model->api_decode($location);

            if (array_key_exists('id', $location)) {
                unset($location['id']);
            }

            $location_id = $this->locations_model->save($location);

            $created_location = $this->locations_model->find($location_id);

            $this->webhooks_client->trigger(WEBHOOK_LOCATION_SAVE, $created_location);

            $this->locations_model->api_encode($created_location);

            json_response($created_location, 201);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Update a location.
     *
     * @param int $id Location ID.
     */
    public function update(int $id): void
    {
        try {
            $occurrences = $this->locations_model->get(['id' => $id]);

            if (empty($occurrences)) {
                response('', 404);

                return;
            }

            $original_location = $occurrences[0];

            $location = request();

            $this->locations_model->api_decode($location, $original_location);

            // The ID in the URL is authoritative, a different one in the body must not redirect the update
            $location['id'] = $id;

            $location_id = $this->locations_model->save($location);

            $updated_location = $this->locations_model->find($location_id);

            $this->webhooks_client->trigger(WEBHOOK_LOCATION_SAVE, $updated_location);

            $this->locations_model->api_encode($updated_location);

            json_response($updated_location);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Delete a location.
     *
     * @param int $id Location ID.
     */
    public function destroy(int $id): void
    {
        try {
            $occurrences = $this->locations_model->get(['id' => $id]);

            if (empty($occurrences)) {
                response('', 404);

                return;
            }

            $deleted_location = $occurrences[0];

            $this->locations_model->delete($id);

            $this->webhooks_client->trigger(WEBHOOK_LOCATION_DELETE, $deleted_location);

            response('', 204);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }
}
