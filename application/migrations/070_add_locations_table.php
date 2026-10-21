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

class Migration_Add_locations_table extends EA_Migration
{
    /**
     * Upgrade method.
     */
    public function up(): void
    {
        if ($this->db->table_exists('locations')) {
            return;
        }

        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '256',
                'null' => false,
            ],
            'address' => [
                'type' => 'TEXT',
                'default' => NULL,
                'null' => true,
            ],
            'city' => [
                'type' => 'VARCHAR',
                'constraint' => '256',
                'default' => NULL,
                'null' => true,
            ],
            'state' => [
                'type' => 'VARCHAR',
                'constraint' => '256',
                'default' => NULL,
                'null' => true,
            ],
            'zip' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'default' => NULL,
                'null' => true,
            ],
            'create_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'update_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('locations', true, ['engine' => 'InnoDB']);

        $this->db->query('
            INSERT INTO `'.$this->db->dbprefix('locations').'` (`name`, `create_datetime`, update_datetime)
            SELECT DISTINCT TRIM(`location`), NOW(), NOW() FROM `'.$this->db->dbprefix('services').'`
        ');

        $this->dbforge->add_column('services', [
            'id_locations' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
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
        if (!$this->db->table_exists('locations')) {
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

        $this->dbforge->drop_table('locations');

        $this->dbforge->drop_column('services', 'id_locations');
    }
}
