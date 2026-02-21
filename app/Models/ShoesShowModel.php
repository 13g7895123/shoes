<?php

namespace App\Models;

/**
 * 展示用資料表 Model（shoes_show_inf）
 *
 * 結構與 ShoesModel 相同，繼承 BaseShoeModel 共用所有定義。
 * 此表每次爬蟲執行後完整替換（TRUNCATE → INSERT）。
 */
class ShoesShowModel extends BaseShoeModel
{
    protected $table = 'shoes_show_inf';
}
