<?php

namespace Tests\Feature;

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../libs/ACL.php';

use PHPUnit\Framework\TestCase;

class AreaAccessControlTest extends TestCase
{
    public function test_area_mapping_covers_all_21_machine_modules_once(): void
    {
		$expected = array(
			'compounding' => array('cosmec', 'fbd_jaw_chuan', 'fbd_glatt', 'supermixer', 'granulator', 'storage_tank', 'storage_tank_tetrapak', 'mixing_tank'),
			'filling' => array('sig', 'joeya', 'illapak_1_2', 'illapak_3_12', 'unifill_b'),
			'kemas' => array('jihcheng', 'jinsung_1_4', 'jinsung_5'),
			'wrapping dan pack cartoning' => array('chimei', 'temach', 'best_pack', 'check_weigher', 'conveyor_sig'),
		);
        $machines = array_merge(...array_values(\ACL::$area_machines));

		$this->assertSame($expected, \ACL::$area_machines);
        $this->assertCount(21, $machines);
        $this->assertCount(21, array_unique($machines));
    }

    public function test_staff_and_operator_are_restricted_to_assigned_area(): void
    {
        $this->assertTrue(\ACL::is_machine_allowed('cosmec', 4, '  COMPOUNDING  '));
        $this->assertFalse(\ACL::is_machine_allowed('sig', 4, 'Compounding'));
        $this->assertTrue(\ACL::is_machine_allowed('chimei/sign_period', 5, ' Wrapping   dan Pack Cartoning '));
        $this->assertFalse(\ACL::is_machine_allowed('jinsung_5', 5, 'Filling'));
        $this->assertFalse(\ACL::is_machine_allowed('sig', 4, ''));
        $this->assertFalse(\ACL::is_machine_allowed('sig', 5, 'Area Tidak Dikenal'));
    }

    public function test_admin_manager_and_supervisor_are_not_area_restricted(): void
    {
        foreach (array(1, 2, 3) as $role) {
            $this->assertTrue(\ACL::is_machine_allowed('sig', $role, 'Compounding'));
            $this->assertTrue(\ACL::is_machine_allowed('cosmec', $role, ''));
        }
    }

	public function test_verify_is_public_and_operator_cancel_route_is_registered(): void
	{
		$this->assertContains('verify', \ACL::$exclude_page_check);
		foreach (array(4, 5) as $role) {
			foreach (array_merge(...array_values(\ACL::$area_machines)) as $machine) {
				$this->assertContains('cancel_period_signature', \ACL::$role_pages[$role][$machine]);
			}
		}
	}
}
