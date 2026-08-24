<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Area;
use App\Models\Material;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\User;
use App\Models\UnitUtilization;
use App\Models\DailyTarget;

class FakeDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. AREAS
        $areaNames = ['Pit A','Pit B','Pit C','Pit D','Pit E','Pit F','Disposal 1','Disposal 2','Disposal 3','Hauling Road A','Hauling Road B','Stockpile 1','Stockpile 2','Crusher Area 1','Crusher Area 2','Workshop Area','Fuel Station 1'];
        foreach ($areaNames as $i => $nama) {
            Area::firstOrCreate(['kode' => 'AREA-'.str_pad($i+1,3,'0',STR_PAD_LEFT)], ['nama'=>$nama,'status'=>'active']);
        }
        $areas = Area::all();

        // 2. MATERIALS
        $mats = [
            ['kode'=>'ORE-001','nama'=>'Bauxite Ore (Raw)','kategori'=>'ore','satuan'=>'ton'],
            ['kode'=>'ORE-002','nama'=>'Nickel Ore','kategori'=>'ore','satuan'=>'ton'],
            ['kode'=>'WST-001','nama'=>'Overburden','kategori'=>'waste','satuan'=>'bcm'],
            ['kode'=>'WST-002','nama'=>'Mining Tuff','kategori'=>'waste','satuan'=>'ton'],
            ['kode'=>'FUL-001','nama'=>'Solar B35','kategori'=>'fuel','satuan'=>'liter'],
        ];
        foreach ($mats as $m) { Material::firstOrCreate(['kode'=>$m['kode']], $m); }
        $materials = Material::whereIn('kategori',['ore','waste'])->get();

        // 3. UNITS
        $unitData = [
            ['kode'=>'DT-001','nama'=>'Komatsu HD785-7','tipe'=>'dump_truck','merk'=>'Komatsu','model'=>'HD785-7','tahun'=>2019,'kapasitas'=>91],
            ['kode'=>'DT-002','nama'=>'Komatsu HD785-7','tipe'=>'dump_truck','merk'=>'Komatsu','model'=>'HD785-7','tahun'=>2019,'kapasitas'=>91],
            ['kode'=>'DT-003','nama'=>'Komatsu HD785-7','tipe'=>'dump_truck','merk'=>'Komatsu','model'=>'HD785-7','tahun'=>2020,'kapasitas'=>91],
            ['kode'=>'DT-004','nama'=>'Komatsu HD785-7','tipe'=>'dump_truck','merk'=>'Komatsu','model'=>'HD785-7','tahun'=>2020,'kapasitas'=>91],
            ['kode'=>'DT-005','nama'=>'CAT 777G','tipe'=>'dump_truck','merk'=>'Caterpillar','model'=>'777G','tahun'=>2021,'kapasitas'=>100],
            ['kode'=>'DT-006','nama'=>'CAT 777G','tipe'=>'dump_truck','merk'=>'Caterpillar','model'=>'777G','tahun'=>2022,'kapasitas'=>100],
            ['kode'=>'EXC-001','nama'=>'Komatsu PC2000-8','tipe'=>'excavator','merk'=>'Komatsu','model'=>'PC2000-8','tahun'=>2020],
            ['kode'=>'EXC-002','nama'=>'Komatsu PC2000-8','tipe'=>'excavator','merk'=>'Komatsu','model'=>'PC2000-8','tahun'=>2021],
            ['kode'=>'EXC-003','nama'=>'Hitachi EX2600','tipe'=>'excavator','merk'=>'Hitachi','model'=>'EX2600','tahun'=>2022],
            ['kode'=>'BLD-001','nama'=>'Komatsu D375A-8','tipe'=>'bulldozer','merk'=>'Komatsu','model'=>'D375A-8','tahun'=>2019],
            ['kode'=>'BLD-002','nama'=>'CAT D9T','tipe'=>'bulldozer','merk'=>'Caterpillar','model'=>'D9T','tahun'=>2021],
            ['kode'=>'MG-001','nama'=>'CAT 16M3','tipe'=>'motor_grader','merk'=>'Caterpillar','model'=>'16M3','tahun'=>2020],
        ];
        foreach ($unitData as $u) {
            $u['status'] = 'active'; $u['is_active'] = true; $u['fuel_consumption_rate'] = rand(25,45);
            Unit::firstOrCreate(['kode'=>$u['kode']], $u);
        }
        $allUnits     = Unit::all();
        $dumpTrucks   = Unit::where('tipe','dump_truck')->get();
        $supportUnits = Unit::whereIn('tipe',['excavator','bulldozer','motor_grader'])->get();

        // 4. PEGAWAI (66)
        $firstNames = ['Budi','Joko','Rudi','Agus','Hendra','Eko','Ahmad','Dedi','Bambang','Wawan','Andi','Iwan','Arief','Hasan','Fajar','Aditya','Rizky','Dimas','Ilham','Taufik','Surya','Bagas','Fauzi','Yusuf','Riyan','Galang','Wahyu','Teguh','Lukman','Syahrul'];
        $lastNames  = ['Santoso','Wijaya','Kurniawan','Setiawan','Nugroho','Pratama','Saputra','Wibowo','Hidayat','Putra','Mahendra','Syahputra','Ramadhan','Fadillah','Pangestu'];
        for ($i=1;$i<=66;$i++) {
            Pegawai::firstOrCreate(['nama'=>$firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)].' '.$i]);
        }
        $pegawai = Pegawai::all();

        // 5. USERS
        User::updateOrCreate(['username'=>'admin'],['name'=>'Super Admin','password'=>Hash::make('password'),'role'=>'admin']);
        User::updateOrCreate(['username'=>'spv'],['name'=>'SPV Utama','password'=>Hash::make('password'),'role'=>'spv']);
        User::updateOrCreate(['username'=>'operator'],['name'=>'Operator Demo','password'=>Hash::make('password'),'role'=>'pegawai','pegawai_id'=>$pegawai->first()?->id]);

        $spvList = [
            ['name'=>'Bpk. Sugiantoro','username'=>'spv1'],
            ['name'=>'Bpk. Darmawan',  'username'=>'spv2'],
            ['name'=>'Bpk. Hendrawan', 'username'=>'spv3'],
            ['name'=>'Bpk. Prasetyo',  'username'=>'spv4'],
            ['name'=>'Bpk. Kurniawan', 'username'=>'spv5'],
            ['name'=>'Bpk. Wibisono',  'username'=>'spv6'],
            ['name'=>'Bpk. Raharjo',   'username'=>'spv7'],
            ['name'=>'Bpk. Gunawan',   'username'=>'spv8'],
        ];
        foreach ($spvList as $i => $s) {
            $spv = User::updateOrCreate(['username'=>$s['username']],[
                'name'=>$s['name'],'password'=>Hash::make('password'),'role'=>'spv','pegawai_id'=>$pegawai->skip($i)->first()?->id,
            ]);
            $spv->areas()->sync($areas->random(rand(2,4))->pluck('id')->toArray());
        }

        $opNames = ['Satriawan','Purnomo','Handoko','Suryanto','Widodo','Setiawan','Hartono','Gunawan','Saputra','Firmansyah','Pradhana','Santosa','Rachman','Subagyo','Hermawan','Supriyadi','Mulyadi','Wicaksono','Budiman','Sutrisno'];
        $operators = [];
        foreach ($opNames as $i => $name) {
            $op = User::updateOrCreate(['username'=>'operator'.($i+1)],[
                'name'=>'Operator '.$name,'password'=>Hash::make('password'),'role'=>'pegawai','pegawai_id'=>$pegawai->skip($i+8)->first()?->id,
            ]);
            $operators[] = $op;
        }

        // 6. UNIT UTILIZATION
        $descBD = ['Unit macet di lokasi','Ganti selang hidrolik bocor','Overheat engine','Kerusakan ban/track','Ganti komponen hidrolik','Kerusakan transmisi'];
        $descSV = ['Perawatan berkala (PMP)','Ganti oli mesin & filter','Servis mesin lengkap','Perbaikan undercarriage','Servis transmisi','Kalibrasi komponen hidrolik'];

        // DT-001 & DT-002: active breakdown (operator1 reported)
        $inMaintenanceUnits = $allUnits->take(2);
        foreach ($inMaintenanceUnits as $u) {
            $prevStart = now()->subDays(rand(5,10))->setTime(7,0)->addHours(rand(0,4));
            UnitUtilization::create(['unit_id'=>$u->id,'status'=>'breakdown','started_at'=>$prevStart,'ended_at'=>$prevStart->copy()->addHours(rand(8,16)),'deskripsi'=>$descBD[array_rand($descBD)],'user_id'=>$operators[0]->id]);
            UnitUtilization::create(['unit_id'=>$u->id,'status'=>'breakdown','started_at'=>now()->subDays(1)->setTime(8,0),'ended_at'=>null,'deskripsi'=>$descBD[array_rand($descBD)],'user_id'=>$operators[0]->id]);
        }
        // DT-003: active servis (operator2 reported)
        $srvUnit = $allUnits->skip(2)->first();
        if ($srvUnit) {
            UnitUtilization::create(['unit_id'=>$srvUnit->id,'status'=>'servis','started_at'=>now()->subDays(1)->setTime(6,30),'ended_at'=>null,'deskripsi'=>$descSV[array_rand($descSV)],'user_id'=>$operators[1]->id]);
        }
        // Rest: historical completed maintenance
        foreach ($allUnits->skip(3) as $u) {
            if (rand(0,100)>=60) continue;
            $bdStart=now()->subDays(rand(3,14))->setTime(7,0)->addHours(rand(0,3));
            $bdEnd=$bdStart->copy()->addHours(rand(6,14));
            $svStart=$bdEnd; $svEnd=$svStart->copy()->addHours(rand(18,48));
            $rep=$operators[array_rand($operators)];
            UnitUtilization::create(['unit_id'=>$u->id,'status'=>'breakdown','started_at'=>$bdStart,'ended_at'=>$bdEnd,'deskripsi'=>$descBD[array_rand($descBD)],'user_id'=>$rep->id]);
            UnitUtilization::create(['unit_id'=>$u->id,'status'=>'servis','started_at'=>$svStart,'ended_at'=>$svEnd,'deskripsi'=>$descSV[array_rand($descSV)],'user_id'=>$rep->id]);
            UnitUtilization::create(['unit_id'=>$u->id,'status'=>'ready','started_at'=>$svEnd->copy()->addMinutes(rand(5,30)),'ended_at'=>null,'deskripsi'=>'Selesai maintenance, unit siap operasi','user_id'=>$rep->id]);
        }

        // 7. RITASI (30 days)
        $availDT = $dumpTrucks->whereNotIn('id',$inMaintenanceUnits->pluck('id'));
        if ($availDT->isEmpty()) $availDT = $dumpTrucks;
        $lokasi = ['Pit 1 North','Pit 2 South','East Dump','Haul Road A','South Pit','Stockpile 1'];
        $deskR  = ['Hauling ore','Hauling waste','Hauling overburden','Hauling topsoil'];
        $shifts = ['siang','malam'];
        $rRows  = [];
        for ($day=0;$day<30;$day++) {
            $tgl=now()->subDays($day)->format('Y-m-d');
            foreach ($shifts as $sh) {
                $shOps=collect($operators)->shuffle()->take(rand(6,min(12,count($operators))));
                foreach ($shOps as $op) {
                    if (!$op->pegawai_id) continue;
                    $unit=$availDT->random(); $mat=$materials->random();
                    $hmA=rand(8000,16000)+(rand(0,99)/100); $hmT=rand(6,11)+(rand(0,99)/100); $rit=rand(8,25);
                    $rRows[]=['pegawai_id'=>$op->pegawai_id,'unit_id'=>$unit->id,'area_id'=>$areas->random()->id,'material_id'=>$mat->id,'tanggal'=>$tgl,'shift'=>$sh,'hm_awal'=>$hmA,'hm_akhir'=>$hmA+$hmT,'hm_total'=>$hmT,'jumlah_ritasi'=>$rit,'quantity'=>$rit*rand(40,90),'quantity_unit'=>'ton','fuel_consumption'=>round($hmT*rand(25,45),2),'lokasi_pekerjaan'=>$lokasi[array_rand($lokasi)],'deskripsi_pekerjaan'=>$deskR[array_rand($deskR)],'status'=>$day>2?'validated':'pending','created_at'=>now(),'updated_at'=>now()];
                }
            }
        }
        foreach (array_chunk($rRows,500) as $chunk) DB::table('ritasis')->insert($chunk);

        // 8. NON-RITASI / GENERAL (30 days)
        $availSup=$supportUnits->whereNotIn('id',$inMaintenanceUnits->pluck('id'));
        if ($availSup->isEmpty()) $availSup=$supportUnits;
        $deskG=['Dozing','Grading','Drilling','Excavating','Loading','Pushing','Maintaining haul road'];
        $deskNR=['Perbaikan jalan produksi','Perataan area disposal','Pembersihan area pit','Penggarukan overburden'];
        $jamM=['06:00','07:00','18:00','19:00']; $jamS=['17:00','18:00','05:00','06:00'];
        $nrRows=[];
        for ($day=0;$day<30;$day++) {
            $tgl=now()->subDays($day)->format('Y-m-d');
            foreach ($shifts as $sh) {
                $shOps=collect($operators)->shuffle()->take(rand(4,min(8,count($operators))));
                $used=[];
                foreach ($shOps as $op) {
                    if (isset($used[$op->id])||!$op->pegawai_id) continue;
                    $used[$op->id]=true;
                    $unit=$availSup->random(); $isG=rand(0,1)===1;
                    $hmA=rand(5000,14000)+(rand(0,99)/100); $hmT=rand(6,11)+(rand(0,99)/100);
                    $nrRows[]=['pegawai_id'=>$op->pegawai_id,'unit_id'=>$unit->id,'area_id'=>$areas->random()->id,'tanggal'=>$tgl,'shift'=>$sh,'hm_awal'=>$hmA,'hm_akhir'=>$hmA+$hmT,'hm_total'=>$hmT,'jam_mulai'=>$isG?$jamM[array_rand($jamM)]:null,'jam_selesai'=>$isG?$jamS[array_rand($jamS)]:null,'is_overtime'=>$isG?(rand(0,100)<20):false,'lokasi_pekerjaan'=>$lokasi[array_rand($lokasi)],'deskripsi_pekerjaan'=>$isG?$deskG[array_rand($deskG)]:$deskNR[array_rand($deskNR)],'status'=>$day>2?'validated':'pending','created_at'=>now(),'updated_at'=>now()];
                }
            }
        }
        foreach (array_chunk($nrRows,500) as $chunk) DB::table('non_ritasis')->insert($chunk);

        // 9. DAILY TARGETS
        $allMat=Material::all();
        foreach ($allMat as $mat) {
            foreach (['harian','mingguan','bulanan'] as $p) {
                $t=match($p){'harian'=>rand(50,150),'mingguan'=>rand(300,900),'bulanan'=>rand(1500,3600)};
                DailyTarget::updateOrCreate(['material_id'=>$mat->id,'periode'=>$p],['target_ritasi'=>$t]);
            }
        }

        $this->command->info('=== FakeDataSeeder berhasil! ===');
        $this->command->info('Login: admin/spv1-8/operator1-20, password: password');
        $this->command->info('DT-001 & DT-002: breakdown aktif (dilaporkan operator1)');
        $this->command->info('DT-003: servis aktif (dilaporkan operator2)');
    }
}
