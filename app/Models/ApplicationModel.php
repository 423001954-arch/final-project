<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationModel extends Model
{
    public function getMenuCategory($menuCategoryID = false)
    {
        if ($menuCategoryID) {
            return $this->db->table('user_menu_category')
                ->where(['id' => $menuCategoryID['id']])
                ->get()
                ->getRowArray();
        }

        return $this->db->table('user_menu_category')
            ->get()
            ->getResultArray();
    }

    public function getMenu($menuID = false)
    {
        if ($menuID) {
            return $this->db->table('user_menu')
                ->select('*, user_menu_category.menu_category AS category, user_menu.menu_category AS menu_category_id, user_menu.id AS menu_id')
                ->join('user_menu_category', 'user_menu.menu_category = user_menu_category.id')
                ->where(['id' => $menuID['menu_id']])
                ->get()
                ->getRowArray();
        }

        return $this->db->table('user_menu')
            ->select('*, user_menu_category.menu_category AS category, user_menu.menu_category AS menu_category_id, user_menu.id AS menu_id')
            ->join('user_menu_category', 'user_menu.menu_category = user_menu_category.id')
            ->get()
            ->getResultArray();
    }

    public function getSubmenu()
    {
        return $this->db->table('user_submenu')
            ->select('*, user_menu.title AS menu_title, user_submenu.menu AS menu_id, user_submenu.id AS submenu_id, user_submenu.title AS submenu_title, user_submenu.url AS submenu_url')
            ->join('user_menu', 'user_submenu.menu = user_menu.id')
            ->join('user_menu_category', 'user_menu.menu_category = user_menu_category.id')
            ->get()
            ->getResultArray();
    }

    public function createMenuCategory($dataMenuCategory)
    {
        $this->db->transBegin();

        $this->db->table('user_menu_category')->insert([
            'menu_category' => $dataMenuCategory['inputMenuCategory']
        ]);

        $menuCategoryID = $this->db->insertID();

        $this->db->table('user_access')->insert([
            'role_id'          => 1,
            'menu_category_id' => $menuCategoryID
        ]);

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transCommit();
        return true;
    }

    public function updateMenuCategory($menuCategoryID)
    {
        return $this->db->table('user_menu_category')
            ->update(['menu_category' => $menuCategoryID['inputMenuCategory']]);
    }

    public function createMenu($dataMenu)
    {
        $this->db->transBegin();

        $this->db->table('user_menu')->insert([
            'menu_category' => $dataMenu['inputMenuCategory2'],
            'title'         => $dataMenu['inputMenuTitle'],
            'url'           => $dataMenu['inputMenuURL'],
            'icon'          => $dataMenu['inputMenuIcon'],
            'parent'        => 0,
        ]);

        $menuID = $this->db->insertID();

        $this->db->table('user_access')->insert([
            'role_id' => 1,
            'menu_id' => $menuID
        ]);

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transCommit();
        return true;
    }

    public function createSubMenu($dataSubmenu)
    {
        $this->db->transBegin();

        $this->db->table('user_submenu')->insert([
            'menu'  => $dataSubmenu['inputMenu'],
            'title' => $dataSubmenu['inputSubmenuTitle'],
            'url'   => $dataSubmenu['inputSubmenuURL']
        ]);

        $submenuID = $this->db->insertID();

        $this->db->table('user_access')->insert([
            'role_id'    => 1,
            'submenu_id' => $submenuID
        ]);

        $this->db->table('user_menu')->update(
            ['parent' => 1],
            ['id' => $dataSubmenu['inputMenu']]
        );

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transCommit();
        return true;
    }

    public function getMenuByUrl($menuUrl)
    {
        return $this->db->table('user_menu')
            ->where(['url' => $menuUrl])
            ->get()
            ->getRowArray();
    }

    public function getUser($username = false, $userID = false)
    {
        $builder = $this->db->table('users')
            ->select('users.*, users.id AS userID, user_role.role_name')
            ->join('user_role', 'users.role_id = user_role.id', 'left');

        if ($username) {
            return $builder
                ->where(['users.email' => $username])
                ->get()
                ->getRowArray();
        } elseif ($userID) {
            return $builder
                ->where(['users.id' => $userID])
                ->get()
                ->getRowArray();
        }

        return $builder
            ->get()
            ->getResultArray();
    }

    public function getAccessMenuCategory($role)
    {
        return $this->db->table('user_menu_category')
            ->select('*, user_menu_category.id AS menuCategoryID')
            ->join('user_access', 'user_menu_category.id = user_access.menu_category_id')
            ->where(['user_access.role_id' => $role])
            ->get()
            ->getResultArray();
    }

    public function getAccessMenu($role)
    {
        return $this->db->table('user_menu')
            ->join('user_access', 'user_menu.id = user_access.menu_id')
            ->where(['user_access.role_id' => $role])
            ->get()
            ->getResultArray();
    }

    public function getUserRole($role = false)
    {
        if ($role) {
            return $this->db->table('user_role')
                ->where(['id' => $role])
                ->get()
                ->getRowArray();
        }

        return $this->db->table('user_role')
            ->get()
            ->getResultArray();
    }

    public function createUser($dataUser)
    {
        return $this->db->table('users')->insert([
            'name'       => $dataUser['inputFullname'],
            'email'      => $dataUser['inputUsername'],
            'password'   => password_hash($dataUser['inputPassword'], PASSWORD_DEFAULT),
            'role_id'    => $dataUser['inputRole'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateUser($dataUser)
    {
        if (!empty($dataUser['inputPassword'])) {
            $password = password_hash($dataUser['inputPassword'], PASSWORD_DEFAULT);
        } else {
            $user = $this->getUser(userID: $dataUser['userID']);
            $password = $user['password'];
        }

        return $this->db->table('users')->update([
            'name'     => $dataUser['inputFullname'],
            'email'    => $dataUser['inputUsername'],
            'password' => $password,
            'role_id'  => $dataUser['inputRole'],
        ], ['id' => $dataUser['userID']]);
    }

    public function deleteUser($userID)
    {
        return $this->db->table('users')->delete(['id' => $userID]);
    }

    public function createRole($dataRole)
    {
        return $this->db->table('user_role')
            ->insert(['role_name' => $dataRole['inputRoleName']]);
    }

    public function updateRole($dataRole)
    {
        return $this->db->table('user_role')
            ->update(['role_name' => $dataRole['inputRoleName']], ['id' => $dataRole['roleID']]);
    }

    public function deleteRole($role)
    {
        return $this->db->table('user_role')
            ->delete(['id' => $role]);
    }

    public function checkUserMenuCategoryAccess($dataAccess)
    {
        return $this->db->table('user_access')
            ->where([
                'role_id'          => $dataAccess['roleID'],
                'menu_category_id' => $dataAccess['menuCategoryID']
            ])
            ->countAllResults();
    }

    public function checkUserAccess($dataAccess)
    {
        return $this->db->table('user_access')
            ->where([
                'role_id' => $dataAccess['roleID'],
                'menu_id' => $dataAccess['menuID']
            ])
            ->countAllResults();
    }

    public function checkUserSubmenuAccess($dataAccess)
    {
        return $this->db->table('user_access')
            ->where([
                'role_id'    => $dataAccess['roleID'],
                'submenu_id' => $dataAccess['submenuID']
            ])
            ->countAllResults();
    }

    public function insertMenuCategoryPermission($dataAccess)
    {
        return $this->db->table('user_access')->insert([
            'role_id'          => $dataAccess['roleID'],
            'menu_category_id' => $dataAccess['menuCategoryID']
        ]);
    }

    public function deleteMenuCategoryPermission($dataAccess)
    {
        return $this->db->table('user_access')->delete([
            'role_id'          => $dataAccess['roleID'],
            'menu_category_id' => $dataAccess['menuCategoryID']
        ]);
    }

    public function insertMenuPermission($dataAccess)
    {
        return $this->db->table('user_access')->insert([
            'role_id' => $dataAccess['roleID'],
            'menu_id' => $dataAccess['menuID']
        ]);
    }

    public function deleteMenuPermission($dataAccess)
    {
        return $this->db->table('user_access')->delete([
            'role_id' => $dataAccess['roleID'],
            'menu_id' => $dataAccess['menuID']
        ]);
    }

    public function insertSubmenuPermission($dataAccess)
    {
        return $this->db->table('user_access')->insert([
            'role_id'    => $dataAccess['roleID'],
            'submenu_id' => $dataAccess['submenuID']
        ]);
    }

    public function deleteSubmenuPermission($dataAccess)
    {
        return $this->db->table('user_access')->delete([
            'role_id'    => $dataAccess['roleID'],
            'submenu_id' => $dataAccess['submenuID']
        ]);
    }
}