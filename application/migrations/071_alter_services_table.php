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

class Migration_Alter_services_table extends EA_Migration
{
    /**
     * Upgrade method.
     */
    public function up(): void
    {
        if (!$this->db->table_exists('services')) {
            return;
        }

        $this->dbforge->add_column('services', [
            'id_locations' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => null,
                'null' => true,
                'after' => 'color',
            ],
        ]);

        $this->db->query('UPDATE `'.$this->db->dbprefix('services').'` SET `id_locations` = (SELECT `id` FROM `'.$this->db->dbprefix('locations').'` WHERE `name` = `location`)');

        $this->dbforge->drop_column('services', 'location');
    }

    /**
     * Downgrade method.
     */
    public function down(): void
    {
        if (!$this->db->table_exists('services')) {
            return;
        }

        $this->dbforge->add_column('services', [
            'location' => [
                'type' => 'TEXT',
                'default' => NULL,
                'null' => true,
                'after' => 'color',
            ],
        ]);

        $this->db->query('UPDATE `'.$this->db->dbprefix('services').'` SET `location` = (SELECT `name` FROM `'.$this->db->dbprefix('locations').'` WHERE `id_locations` = `id`)');

        $this->dbforge->drop_column('services', 'id_locations');
    }
}
