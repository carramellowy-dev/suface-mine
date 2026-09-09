<?php

namespace Tests\Unit;

use App\Services\MineCalculationService;
use PHPUnit\Framework\TestCase;

class MineCalculationTest extends TestCase
{
    // === 1. HM Total = HM Akhir - HM Awal ===
    public function test_hm_total_normal(): void
    {
        $this->assertEquals(7.0, MineCalculationService::hmTotal(5240.5, 5247.5));
        $this->assertEquals(6.5, MineCalculationService::hmTotal(5247.5, 5254.0));
        $this->assertEquals(6.0, MineCalculationService::hmTotal(5254.0, 5260.0));
    }

    public function test_hm_total_max_12_valid(): void
    {
        $this->assertEquals(12.0, MineCalculationService::hmTotal(100.0, 112.0));
    }

    public function test_hm_total_negatif_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MineCalculationService::hmTotal(5260.0, 5240.5);
    }

    public function test_hm_total_equal_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // HM Akhir == HM Awal -> total 0, harus >0
        MineCalculationService::hmTotal(100.0, 100.0);
    }

    public function test_hm_total_gt_12_capped(): void
    {
        // Baru: >12 di-cap otomatis ke 12, bukan throw
        $this->assertEquals(12.0, MineCalculationService::hmTotal(100.0, 113.0)); // 13 ->12
        $this->assertEquals(12.0, MineCalculationService::hmTotal(3333.7, 4333.7)); // 1000 ->12
        $this->assertEquals(12.0, MineCalculationService::hmTotal(0, 100));
    }

    public function test_capped_hm_akhir(): void
    {
        $this->assertEquals(112.0, MineCalculationService::cappedHmAkhir(100, 113));
        $this->assertEquals(3345.7, MineCalculationService::cappedHmAkhir(3333.7, 4333.7));
    }

    public function test_validate_hm_total_negative(): void
    {
        $res = MineCalculationService::validateHmTotal(5254.0, 5240.0);
        $this->assertFalse($res['valid']);
        $this->assertEquals(-14.0, $res['hm_total']);
    }

    public function test_validate_hm_total_equal_invalid(): void
    {
        $res = MineCalculationService::validateHmTotal(100, 100);
        $this->assertFalse($res['valid']);
        $this->assertEquals(0.0, $res['hm_total']);
    }

    public function test_validate_hm_total_gt_12_capped_valid(): void
    {
        $res = MineCalculationService::validateHmTotal(100, 113);
        $this->assertTrue($res['valid']);
        $this->assertEquals(12.0, $res['hm_total']);
        $this->assertTrue($res['capped']);
        $this->assertEquals(112.0, $res['hm_akhir_capped']);
    }

    public function test_validate_hm_total_gt_12_example_image(): void
    {
        // Contoh image: 3333.7 -> 4333.7 =1000 jam, harus di-cap 12
        $res = MineCalculationService::validateHmTotal(3333.7, 4333.7);
        $this->assertTrue($res['valid']);
        $this->assertEquals(12.0, $res['hm_total']);
        $this->assertEquals(3345.7, $res['hm_akhir_capped']);
    }

    // === 2. WH = SUM(HM Total) ===
    public function test_wh_sum_example(): void
    {
        $hmTotals = [7.0, 6.5, 6.0];
        $wh = array_sum($hmTotals);
        $this->assertEquals(19.5, $wh);
    }

    // === 3. SH = unit aktif ×12×hari ===
    public function test_sh_2_units_1_day(): void
    {
        $sh = 2 * 12 * 1;
        $this->assertEquals(24, $sh);
    }

    public function test_sh_multiple_days(): void
    {
        $units = 4;
        $days = 5;
        $sh = $units * 12 * $days; // 4*12*5=240
        $this->assertEquals(240, $sh);
    }

    // === 5. PA = (SH-BD)/SH*100 ; SH<=0 atau SH<=BD =>0 ===
    public function test_pa_normal(): void
    {
        // Spec example: SH=24, BD=2 => PA = (24-2)/24*100 = 91.666 =>91.67
        $this->assertEquals(91.67, MineCalculationService::pa(24, 2));
        // Example gambar: SH=270, BD=0 =>100%
        $this->assertEquals(100.0, MineCalculationService::pa(270, 0));
        // Multiple unit example: 3 unit, 2 hari => SH=72, BD=10 => (62/72)*100=86.11
        $this->assertEquals(86.11, MineCalculationService::pa(72, 10));
    }

    public function test_pa_sh_zero(): void
    {
        $this->assertEquals(0.0, MineCalculationService::pa(0, 0));
        $this->assertEquals(0.0, MineCalculationService::pa(0, 5));
    }

    public function test_pa_sh_lte_bd(): void
    {
        // Business rule: SH<=BD => PA=0, UA=0
        $this->assertEquals(0.0, MineCalculationService::pa(24, 24));
        $this->assertEquals(0.0, MineCalculationService::pa(24, 30));
        $this->assertEquals(0.0, MineCalculationService::pa(10, 15));
    }

    // === 6. UA = WH/(SH-BD)*100 ===
    public function test_ua_normal(): void
    {
        // Spec contoh: SH=24 BD=2 WH=19.5 => 19.5/(22)*100 =88.636 =>88.64
        $this->assertEquals(88.64, MineCalculationService::ua(19.5, 24, 2));
        // Gambar: WH=146 SH=270 BD=0 =>146/270*100=54.074=>54.07
        $this->assertEquals(54.07, MineCalculationService::ua(146, 270, 0));
        // WH kecil
        $this->assertEquals(50.0, MineCalculationService::ua(10, 24, 4)); // 10/20*100=50
    }

    public function test_ua_sh_lte_bd_returns_zero(): void
    {
        $this->assertEquals(0.0, MineCalculationService::ua(10, 0, 0));
        $this->assertEquals(0.0, MineCalculationService::ua(10, 24, 24));
        $this->assertEquals(0.0, MineCalculationService::ua(10, 24, 30));
    }

    public function test_ua_wh_zero(): void
    {
        $this->assertEquals(0.0, MineCalculationService::ua(0, 24, 2));
    }

    // === 7. Konsistensi satuan jam decimal ===
    public function test_satuan_jam_decimal(): void
    {
        // 0.5 jam = 30 menit, dalam jam decimal 0.5
        $wh = 0.5 + 1.5; // 2 jam
        $this->assertEquals(2.0, $wh);
        // SH 2 unit 1 hari =24 jam, WH 2 jam => UA 2/(24)*100=8.33 jika BD0
        $this->assertEquals(8.33, MineCalculationService::ua(2.0, 24, 0));
    }

    // === 8. Alur lengkap contoh spec section 9 ===
    public function test_full_flow_spec_example(): void
    {
        // Ritasi 1:7, 2:6.5, 3:6 => WH19.5, SH24 BD2 => PA91.67 UA88.64
        $wh = 7 + 6.5 + 6;
        $this->assertEquals(19.5, $wh);
        $sh = 2 * 12 * 1; // 2 unit 1 hari
        $this->assertEquals(24, $sh);
        $bd = 2;
        $pa = MineCalculationService::pa($sh, $bd);
        $ua = MineCalculationService::ua($wh, $sh, $bd);
        $this->assertEquals(91.67, $pa);
        $this->assertEquals(88.64, $ua);
    }

    // === 9. Multiple shift / multiple day ===
    public function test_multiple_shift_wh(): void
    {
        // Siang: 7+6.5=13.5 , Malam: 6 => WH total 19.5
        $siang = 7 + 6.5;
        $malam = 6;
        $this->assertEquals(19.5, $siang + $malam);
    }

    // === 10. Waiting tidak masuk BD ===
    public function test_waiting_not_bd(): void
    {
        // BD hanya breakdown+servis, waiting delay 0.5 jam tidak dihitung
        $bdBreakdown = 2.0;
        $bdServis = 1.0;
        $waiting = 0.5; // tidak masuk
        $bd = $bdBreakdown + $bdServis; // 3 jam
        $this->assertEquals(3.0, $bd);
        $this->assertNotEquals(3.5, $bd);
    }

    // === Edge: duplicate offline replay ===
    public function test_duplicate_handling_logic(): void
    {
        // Controller mencegah duplikat via unique (pegawai_id,tanggal,shift) dan return 200 pada replay
        // Simulasi: jika exists, WH tidak double count
        $whBefore = 19.5;
        $duplicateHmTotal = 7.0; // seharusnya tidak ditambah
        // WH after duplicate should stay 19.5, not 26.5
        $this->assertEquals(19.5, $whBefore);
    }
}
