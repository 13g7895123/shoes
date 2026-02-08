<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApiKeyModel;

class ApiKeys extends BaseController
{
    public function index(): string
    {
        $model = new ApiKeyModel();
        $keys = $model->orderBy('id', 'DESC')->findAll();

        return view('admin/api_keys', [
            'keys' => $keys,
            'newKey' => session()->getFlashdata('new_api_key')
        ]);
    }

    public function create()
    {
        $name = trim((string)$this->request->getPost('name'));
        $permission = $this->request->getPost('permission');

        if ($name === '' || $permission === null) {
            return redirect()->back()->with('error', '請填寫名稱與權限');
        }

        $model = new ApiKeyModel();
        $apiKey = $this->generateApiKey();

        $data = [
            'name' => $name,
            'api_key' => $apiKey,
            'permission' => $permission,
            'status' => 'active'
        ];

        if (!$model->insert($data)) {
            return redirect()->back()->with('error', '建立 API Key 失敗');
        }

        return redirect()->to('/admin/api-keys')->with('new_api_key', $apiKey);
    }

    private function generateApiKey(): string
    {
        return 'sk_' . bin2hex(random_bytes(16));
    }
}
