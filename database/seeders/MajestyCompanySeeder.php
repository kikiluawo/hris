<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class MajestyCompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            'MAL006' => 'PT Majesty Auto Lestari',
            'MAP005' => 'PT Majesty Auto Prima',
            'MAW004' => 'PT Majesty Auto World',
            'MAD003' => 'PT Majesty Auto Dinamika',
            'MS016' => 'PT Majesty Sejahtera',
            'MKN015' => 'PT Majesty Kreasi Nusantara',
            'MAO014' => 'PT Majesty Auto Optimal',
            'MAJ013' => 'PT Majesty Auto Jaya',
            'MAV012' => 'PT Majesty Auto Victory',
            'MAS011' => 'PT Majesty Auto Sejahtera',
            'MAG010' => 'PT Majesty Auto Gemilang',
            'EAD009' => 'PT Eurasia Auto Dinamika',
            'MAN008' => 'PT Majesty Auto Nusantara',
            'MAI007' => 'PT Majesty Auto Inovasi',
            'JEN026' => 'PT Jaya Energy Nusantara',
            'PROS025' => 'PT Majesty Prosperindo',
            'TEI024' => 'PT Turbion Energy International',
            'SPB023' => 'PT Majesty Sarana Energy',
            'EBJ022' => 'PT Energi Batam Jaya',
            'MMS021' => 'PT Majesti Makmur Sentosa',
            'BM020' => 'PT Batam Makmur',
            'BS019' => 'PT Batam Sentosa',
            'MPL018' => 'PT Majesty Petroleum',
            'MPT036' => 'PT Majesty Petrolindo',
            'ANS035' => 'PT Andalan Nusantara Suksesindo',
            'MSG034' => 'PT Majesty Sahabat Gadai',
            'MSN033' => 'PT Majesty Sinergi Nusantara',
            'MEN032' => 'PT Majesty Enerbion Nusantara',
            'MGP031' => 'PT Majesty Gadai Prima',
            'MGS030' => 'PT Majesty Global Sejahtera',
            'NJKM029' => 'PT Nusa Jaya Karya Mandiri',
            'GES028' => 'PT Global Energi Sejahtera',
            'PME027' => 'PT Pelayaran Majesty Excelindo',
            'MLF046' => 'PT Majesty Law Firm',
            'MKI045' => 'PT Majesty Kapital Investama',
            'MGM044' => 'PT Majesty Global Motors',
            'MAM043' => 'PT Majesty Auto Megah',
            'MMA042' => 'PT Majesty Mega Automotive',
            'CJR' => 'CV Citra Jaya Raya',
            'IP040' => 'PT Investama Prosperindo',
            'MMSC039' => 'PT Majesty Maritim Service',
            'MMI038' => 'PT Majesty Maritim Indonesia',
            'ECS037' => 'PT Energi Citra Sejahtera',
        ];

        $timestamps = [
            'MPT036' => '2026-06-20 08:43:25',
            'MLF046' => '2026-10-21 09:18:36',
            'MKI045' => '2026-08-28 11:30:41',
            'MGM044' => '2026-08-24 16:22:02',
            'MAM043' => '2026-08-22 07:53:31',
            'MMA042' => '2026-08-14 16:53:13',
            'CJR' => '2026-08-13 09:34:14',
            'IP040' => '2026-07-29 08:50:07',
            'MMSC039' => '2026-07-17 14:05:38',
            'MMI038' => '2026-07-07 06:16:58',
        ];

        foreach ($companies as $code => $name) {
            $company = Company::firstOrNew(['code' => $code]);
            $company->name = $name;
            $company->is_active = true;

            if (! $company->exists && isset($timestamps[$code])) {
                $company->created_at = $timestamps[$code];
                $company->updated_at = $timestamps[$code];
            }

            $company->save();
        }
    }
}
