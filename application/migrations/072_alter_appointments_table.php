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

class Migration_Alter_appointments_table extends EA_Migration
{
    /**
     * Upgrade method.
     */
    public function up(): void
    {
        if (!$this->db->table_exists('appointments')) {
            return;
        }

        $this->dbforge->add_column('appointments', [
            'id_locations' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => null,
                'null' => true,
                'after' => 'end_datetime',
            ],
        ]);

        $this->db->query('UPDATE `'.$this->db->dbprefix('appointments').'` SET `id_locations` = (SELECT `id_locations` FROM `'.$this->db->dbprefix('services').'` WHERE `id` = `id_services`)');

        $this->dbforge->drop_column('appointments', 'location');
    }

    /**
     * Downgrade method.
     */
    public function down(): void
    {
        if (!$this->db->table_exists('locations')) {
            return;
        }

        $this->dbforge->add_column('appointments', [
            'location' => [
                'type' => 'TEXT',
                'default' => NULL,
                'null' => true,
                'after' => 'color',
            ],
        ]);

        $this->db->query('UPDATE `'.$this->db->dbprefix('appointments').'` SET `location` = (SELECT `name` FROM `'.$this->db->dbprefix('locations').'` WHERE `id_locations` = `id`)');

        $this->dbforge->drop_column('appointments', 'id_locations');
    }
}
