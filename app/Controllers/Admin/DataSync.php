<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShoesModel;
use App\Models\ShoesShowModel;

class DataSync extends BaseController
{
    protected $shoesModel;
    protected $showModel;

    public function __construct()
    {
        $this->shoesModel = new ShoesModel();
        $this->showModel  = new ShoesShowModel();
    }

    /**
     * GET /admin/data-sync
     * 顯示修復頁面，並預先偵測不一致筆數
     */
    public function index(): string
    {
        $mismatches = $this->detectMismatches();

        return view('admin/data_sync', [
            'mismatches'     => $mismatches,
            'mismatch_count' => count($mismatches),
            'result'         => session()->getFlashdata('result'),
        ]);
    }

    /**
     * POST /admin/data-sync/run
     * 執行修復：以 shoes_inf.eng_name 更新 shoes_show_inf.eng_name
     */
    public function run()
    {
        $db = \Config\Database::connect();

        try {
            // 取得所有不一致的記錄
            $mismatches = $this->detectMismatches();
            $total      = count($mismatches);
            $updated    = 0;

            if ($total > 0) {
                // 使用單一 UPDATE ... JOIN 一次修復
                $sql = "UPDATE shoes_show_inf AS s
                        INNER JOIN shoes_inf AS i ON s.code = i.code
                        SET s.eng_name = i.eng_name
                        WHERE s.eng_name != i.eng_name
                           OR (s.eng_name IS NULL AND i.eng_name IS NOT NULL)
                           OR (s.eng_name IS NOT NULL AND i.eng_name IS NULL)";

                $db->query($sql);
                $updated = $db->affectedRows();
            }

            return redirect()->to('/admin/data-sync')->with('result', [
                'success' => true,
                'total'   => $total,
                'updated' => $updated,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'DataSync::run error: ' . $e->getMessage());
            return redirect()->to('/admin/data-sync')->with('result', [
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 偵測 shoes_show_inf 與 shoes_inf 之間 eng_name 不一致的記錄
     */
    private function detectMismatches(): array
    {
        $db = \Config\Database::connect();

        $sql = "SELECT
                    s.code,
                    s.name,
                    s.eng_name   AS show_eng_name,
                    i.eng_name   AS inf_eng_name
                FROM shoes_show_inf AS s
                INNER JOIN shoes_inf AS i ON s.code = i.code
                WHERE s.eng_name != i.eng_name
                   OR (s.eng_name IS NULL AND i.eng_name IS NOT NULL)
                   OR (s.eng_name IS NOT NULL AND i.eng_name IS NULL)
                ORDER BY s.code";

        return $db->query($sql)->getResultArray();
    }
}
