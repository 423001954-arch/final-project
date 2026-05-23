<?php

use App\Services\RoleAccess;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RoleAccessTest extends CIUnitTestCase
{
    public function testSuperAdminCanManageUsers(): void
    {
        $this->assertTrue(RoleAccess::canManageUsers('superadmin'));
    }

    public function testManagerCannotManageUsers(): void
    {
        $this->assertFalse(RoleAccess::canManageUsers('manager'));
    }

    public function testStaffCanRequestStockButCannotOperateSupplyChain(): void
    {
        $this->assertTrue(RoleAccess::canRequestStock('staff'));
        $this->assertFalse(RoleAccess::canOperateSupplyChain('staff'));
    }

    public function testRoleNamesAreNormalizedToLowercase(): void
    {
        $this->assertSame('superadmin', RoleAccess::normalize(' SuperAdmin '));
    }

    public function testSuperAdminCanOperateSupplyChain(): void
    {
        $this->assertTrue(RoleAccess::canOperateSupplyChain('superadmin'));
    }

    public function testUnknownRoleHasNoAccess(): void
    {
        $this->assertFalse(RoleAccess::canRequestStock('visitor'));
        $this->assertFalse(RoleAccess::canOperateSupplyChain(null));
    }
}
