<?php

namespace App\Controllers;

use App\Models\HealthcareFacilityModel;
use App\Models\MedicineBatchModel;
use App\Models\MedicineModel;
use App\Models\UserModel;
use Config\Database;

class Home extends BaseController
{
    public function index()
    {
        $data = array_merge($this->data, [
            'title' => 'Healthcare Supply Chain Dashboard',
            'dashboard' => $this->supplyChainDashboardData(),
        ]);

        return view('dashboard', $data);
    }

    private function supplyChainDashboardData(): array
    {
        $db = Database::connect();

        if (! $db->tableExists('healthcare_facilities') || ! $db->tableExists('medicines') || ! $db->tableExists('medicine_batches')) {
            return [
                'is_ready' => false,
                'message'  => 'Run the supply-chain migrations to activate facilities, medicines, batches, and stock movements.',
            ];
        }

        $batchModel = new MedicineBatchModel();

        $activeStock = (int) ($db->table('medicine_batches')
            ->selectSum('available_quantity')
            ->where('status', 'available')
            ->where('expiry_date >', date('Y-m-d'))
            ->get()
            ->getRowArray()['available_quantity'] ?? 0);

        $facilityCount = (new HealthcareFacilityModel())->where('is_active', 1)->countAllResults();
        $medicineCount = (new MedicineModel())->where('status', 'active')->countAllResults();
        $availableBatches = $batchModel->where('status', 'available')
            ->where('available_quantity >', 0)
            ->where('expiry_date >', date('Y-m-d'))
            ->countAllResults();

        $expired = $batchModel->expiredActiveBatches(5);
        $expiringSoon = $db->table('medicine_batches b')
            ->select('b.batch_number, b.expiry_date, b.available_quantity, b.warehouse_location, m.generic_name, f.name AS facility_name')
            ->join('medicines m', 'm.id = b.medicine_id')
            ->join('healthcare_facilities f', 'f.id = m.facility_id', 'left')
            ->where('b.status', 'available')
            ->where('b.available_quantity >', 0)
            ->where('b.expiry_date >', date('Y-m-d'))
            ->where('b.expiry_date <=', date('Y-m-d', strtotime('+30 days')))
            ->orderBy('b.expiry_date', 'ASC')
            ->limit(5)
            ->get()
            ->getResultArray();

        $recentMovements = [];
        if ($db->tableExists('stock_movements')) {
            $recentMovements = $db->table('stock_movements sm')
                ->select('sm.movement_type, sm.quantity, sm.reference_type, sm.reference_id, sm.created_at, m.generic_name, b.batch_number, f.name AS facility_name')
                ->join('medicines m', 'm.id = sm.medicine_id')
                ->join('medicine_batches b', 'b.id = sm.batch_id')
                ->join('healthcare_facilities f', 'f.id = sm.facility_id', 'left')
                ->orderBy('sm.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        }

        return [
            'is_ready' => true,
            'metrics' => [
                'active_stock'      => $activeStock,
                'active_facilities' => $facilityCount,
                'active_medicines'  => $medicineCount,
                'available_batches' => $availableBatches,
                'expired_batches'   => count($expired),
            ],
            'expiring_soon'    => $expiringSoon,
            'expired_batches'  => $expired,
            'recent_movements' => $recentMovements,
            'workflow' => [
                [
                    'title' => 'Supply Intake',
                    'status' => 'Admin records received stock with a unique Batch ID, expiry date, and warehouse location.',
                    'endpoint' => 'POST /api/v1/medicine-batches',
                ],
                [
                    'title' => 'Validation',
                    'status' => 'The API rejects duplicate Batch IDs and expiry dates that are not in the future.',
                    'endpoint' => 'MedicineBatchesController::create',
                ],
                [
                    'title' => 'Storage Logic',
                    'status' => 'Accepted stock is locked to the batch warehouse location and recorded as a receive movement.',
                    'endpoint' => 'stock_movements',
                ],
                [
                    'title' => 'Requisition',
                    'status' => 'Clinic users request a medicine quantity for a facility.',
                    'endpoint' => 'POST /api/v1/stock-allocations',
                ],
                [
                    'title' => 'Smart Fulfillment',
                    'status' => 'FEFO automatically chooses the closest valid expiry batch first.',
                    'endpoint' => 'FefoStockAllocator',
                ],
                [
                    'title' => 'Disposal Protocol',
                    'status' => 'Expired active batches can be flagged with php spark supply:flag-expired.',
                    'endpoint' => 'supply:flag-expired',
                ],
            ],
        ];
    }

    public function dashboardV2()
    {
        $data = array_merge($this->data, [
            'title' => 'Dashboard v2 Page'
        ]);

        return view('dashboard', $data);
    }

    public function dashboardV3()
    {
        $data = array_merge($this->data, [
            'title' => 'Dashboard v3 Page'
        ]);

        return view('dashboard', $data);
    }

    public function profile()
    {
        $username = session()->get('username');

        if (!$username) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $username)->first();

        if (!$user) {
            session()->destroy();
            return redirect()->to('/login');
        }

        $data = array_merge($this->data, [
            'title' => 'My Profile',
            'user'  => $user
        ]);

        return view('profile/show', $data);
    }

    public function editProfile()
    {
        $username = session()->get('username');

        if (!$username) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $username)->first();

        if (!$user) {
            session()->destroy();
            return redirect()->to('/login');
        }

        $data = array_merge($this->data, [
            'title' => 'Edit Profile',
            'user'  => $user
        ]);

        return view('profile/edit', $data);
    }

    public function updateProfile()
    {
        $username = session()->get('username');

        if (!$username) {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $username)->first();

        if (!$user) {
            session()->destroy();
            return redirect()->to('/login');
        }

        $userId = $user['id'];

        $rules = [
            'name'          => 'required|min_length[3]',
            'email'         => "required|valid_email|is_unique[users.email,id,{$userId}]",
            'profile_image' => 'if_exist|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp]|max_size[profile_image,2048]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name'       => $this->request->getPost('name'),
            'email'      => $this->request->getPost('email'),
        ];

        $file = $this->request->getFile('profile_image');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/profiles/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            if (!empty($user['profile_image'])) {
                $oldPath = $uploadPath . $user['profile_image'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $newName = 'avatar_' . $userId . '_' . time() . '.' . $file->getExtension();
            $file->move($uploadPath, $newName);

            service('image')
                ->withFile($uploadPath . $newName)
                ->fit(320, 320, 'center')
                ->save($uploadPath . $newName, 85);

            $updateData['profile_image'] = $newName;
        }

        $userModel->update($userId, $updateData);

        session()->set([
            'user_id'    => $userId,
            'username'   => $updateData['email'],
            'user_name'  => $updateData['name'],
            'isLoggedIn' => true
        ]);

        return redirect()->to('/dashboard/profile')->with('success', 'Profile updated successfully.');
    }
}
