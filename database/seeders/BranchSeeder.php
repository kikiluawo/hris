<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::pluck('id', 'code');

        $branches = [
            ['BM020-BARELANG', 'SPBU Batam Makmur Barelang', 'BM020', 'Pertamina', 'SPBU', 'Batam'],
            ['BM020-TGUNCANG01', 'SPBU Batam Makmur Tanjung Uncang', 'BM020', 'Pertamina', 'SPBU', 'Batam'],
            ['BS019-MEDITERANIA02', 'SPBU Batam Sentosa Mediterania', 'BS019', 'Pertamina', 'SPBU', 'Batam'],
            ['BS019-SIMPANGFRENGKY01', 'SPBU Batam Sentosa Simpang Frengky', 'BS019', 'Pertamina', 'SPBU', 'Batam'],
            ['EAD009-EMAUTO01', 'EmAuto Batam', 'EAD009', 'EmAuto', '3S', 'Batam'],
            ['EBJ022-PUNGGUR', 'SPBU EBJ Punggur', 'EBJ022', 'Pertamina', 'SPBU', 'Batam'],
            ['EBJ022-SEIHARAPAN01', 'SPBU EBJ Sei Harapan', 'EBJ022', 'Pertamina', 'SPBU', 'Batam'],
            ['MAD003-CBU02', 'Emtech Batam', 'MAD003', 'CBU', '3S', 'Batam'],
            ['MAD003-MAZDA01', 'Mazda Batam', 'MAD003', 'Mazda', '3S', 'Batam'],
            ['MAG010-GWM01', 'GWM Batam', 'MAG010', 'GWM', '3S', 'Batam'],
            ['MAI007-I-CAR01', 'I-CAR Batam', 'MAI007', 'I-CAR', '3S', 'Batam'],
            ['MAJ013-JETOURBTM', 'Jetour Batam', 'MAJ013', 'Jetour', '3S', 'Batam'],
            ['MAJ013-JETOURPINANG', 'Jetour Tanjung Pinang', 'MAJ013', 'Jetour', '3S', 'Tanjung Pinang'],
            ['MAL006-KIA01', 'KIA Batam', 'MAL006', 'KIA', '3S', 'Batam'],
            ['MAM043-MERCY01', 'Mercy Batam', 'MAM043', 'Mercy', '1S', 'Batam'],
            ['MAN008-LEPAS01', 'LEPAS Batam', 'MAN008', 'LEPAS', '3S', 'Batam'],
            ['MAO014-DFSK01', 'DFSK Batam', 'MAO014', 'DFSK', '3S', 'Batam'],
            ['MAP005-MG01', 'MG Batam', 'MAP005', 'MG', '3S', 'Batam'],
            ['MAS011-VINFAST01', 'Vinfast Batam', 'MAS011', 'Vinfast', '3S', 'Batam'],
            ['MAV012-VOLVO01', 'Volvo Batam', 'MAV012', 'Volvo', '3S', 'Batam'],
            ['MAW004-CHERYBTM', 'Chery Batam', 'MAW004', 'Chery', '3S', 'Batam'],
            ['MAW004-CHERYMDT', 'Chery Mediterania', 'MAW004', 'Chery', '3S', 'Batam'],
            ['MGM044-EMAUTO01', 'EmAuto', 'MGM044', 'EmAuto', '1S', 'Batam'],
            ['MGP031-BENGKONG01', 'MGP Bengkong', 'MGP031', null, null, 'Batam'],
            ['MGP031-KAPITAL01', 'MGP Kapital', 'MGP031', null, null, 'Batam'],
            ['MGP031-TEMBESI', 'MGP Tembesi', 'MGP031', null, null, 'Batam'],
            ['MMA042-LEPAS01', 'LEPAS Medan', 'MMA042', 'LEPAS', '1S', 'Medan'],
            ['MMS021-BANDARA06', 'SPBU Bandara', 'MMS021', 'Pertamina', 'SPBU', 'Batam'],
            ['MMS021-BATUAJI05', 'SPBU Batu Aji', 'MMS021', 'Pertamina', 'SPBU', 'Batam'],
            ['MMS021-BATUAMPAR01', 'SPBU Makmur Sentosa Batu Ampar', 'MMS021', 'Pertamina', 'SPBU', 'Batam'],
            ['MMS021-PUNGGUR02', 'SPBU Makmur Sentosa Punggur', 'MMS021', 'Pertamina', 'SPBU', 'Batam'],
            ['MMS021-SEIHARAPAN', 'SPBU Makmur Sentosa Sei Harapan', 'MMS021', 'Pertamina', 'SPBU', 'Batam'],
            ['MMS021-SIMPANGFRENGKY', 'SPBU Makmur Sentosa Simpang Frengky', 'MMS021', 'Pertamina', 'SPBU', 'Batam'],
            ['MPL018-BENGKONG02', 'SPBU Petroleum Bengkong (Petroleum 2)', 'MPL018', 'Pertamina', 'SPBU', 'Batam'],
            ['MPL018-ROSEDALE01', 'SPBU Petroleum Rosedale (Petroleum 1)', 'MPL018', 'Pertamina', 'SPBU', 'Batam'],
            ['MPT036-KAPITAL01', 'SPBU Petrolindo Kapital (Petrolindo 1)', 'MPT036', 'Pertamina', 'SPBU', 'Batam'],
            ['MPT036-RADENPATAH02', 'SPBU Petrolindo Raden Patah (Petrolindo 2)', 'MPT036', 'Pertamina', 'SPBU', 'Batam'],
            ['MS016-BCS', 'SPBU Sejahtera BCS (Sejahtera 2)', 'MS016', 'Pertamina', 'SPBU', 'Batam'],
            ['MS016-PLAMO01', 'SPBU Sejahtera Plamo (Sejahtera 1)', 'MS016', 'Pertamina', 'SPBU', 'Batam'],
            ['NJKM029-TURBION01', 'NJKM Lobam Bintan', 'NJKM029', 'Turbion', 'SPBU', 'Bintan'],
            ['PROS025-JAKARTA', 'Prosperindo Jakarta', 'PROS025', 'Fuel', 'SPBU', 'Jakarta'],
            ['PROS025-KARIMUN', 'Prosperindo Tg. Balai Karimun', 'PROS025', 'Fuel', 'SPBU', 'Karimun'],
        ];

        foreach ($branches as [$code, $name, $companyCode, $brand, $type, $city]) {
            Branch::updateOrCreate(
                ['code' => $code],
                [
                    'company_id' => $companies[$companyCode],
                    'name' => $name,
                    'brand' => $brand,
                    'type' => $type,
                    'city' => $city,
                    'is_active' => true,
                ]
            );
        }
    }
}
