<?php

namespace Database\Seeders;

use App\Models\Bribox;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BriboxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $briboxes = [
            ['bribox_id' => 'A1', 'type' => 'PC STANDART', 'bribox_category_id' => 1], // PC
            ['bribox_id' => 'A2', 'type' => 'PC STANDART BDS SERVER', 'bribox_category_id' => 1], // PC
            ['bribox_id' => 'A3', 'type' => 'PC HIGH END', 'bribox_category_id' => 1], // PC
            ['bribox_id' => 'A4', 'type' => 'AIO PC STANDART', 'bribox_category_id' => 1], // PC
            ['bribox_id' => 'A5', 'type' => 'AIO PC HIGH END', 'bribox_category_id' => 1], // PC
            ['bribox_id' => 'A6', 'type' => 'LAYAR LAPTOP', 'bribox_category_id' => 1], // PC
            ['bribox_id' => 'B1', 'type' => 'LAPTOP STANDART', 'bribox_category_id' => 2], // LAPTOP
            ['bribox_id' => 'B2', 'type' => 'LAPTOP HIGH END', 'bribox_category_id' => 2], // LAPTOP
            ['bribox_id' => 'B3', 'type' => 'TABLET STANDART', 'bribox_category_id' => 3], // TABLET
            ['bribox_id' => 'B4', 'type' => 'TABLET HIGH END', 'bribox_category_id' => 3], // TABLET
            ['bribox_id' => 'C0', 'type' => 'PRINTER DATA CARD', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C1', 'type' => 'PASSBOOK PRINTER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C2', 'type' => 'DOT MATRIX PRINTER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C3', 'type' => 'MULTIFUNCTION PRINTER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C4', 'type' => 'LINE PRINTER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C5', 'type' => 'PRINT SERVER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C6', 'type' => 'LASERJET MONO PRINTER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C7', 'type' => 'LASERJET COLOUR PRINTER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C8', 'type' => 'DESKJET PRINTER', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'C9', 'type' => 'LASER PRINTER A3', 'bribox_category_id' => 4], // PRINTER
            ['bribox_id' => 'D1', 'type' => 'SCANNER ADF', 'bribox_category_id' => 5], // SCANNER
            ['bribox_id' => 'D2', 'type' => 'SCANNER DUALBED', 'bribox_category_id' => 5], // SCANNER
            ['bribox_id' => 'E1', 'type' => 'HDD EKSTERNAL 1 TB', 'bribox_category_id' => 6], // STORAGE
            ['bribox_id' => 'F1', 'type' => 'NAS 4 TB', 'bribox_category_id' => 6], // STORAGE
            ['bribox_id' => 'F2', 'type' => 'NAS 6 TB', 'bribox_category_id' => 6], // STORAGE
            ['bribox_id' => 'F3', 'type' => 'NAS 10 TB', 'bribox_category_id' => 6], // STORAGE
            ['bribox_id' => 'F4', 'type' => 'NAS 12 TB', 'bribox_category_id' => 6], // STORAGE
            ['bribox_id' => 'G1', 'type' => 'UPS 1-3 KVA', 'bribox_category_id' => 7], // UPS
            ['bribox_id' => 'G2', 'type' => 'UPS 4-6 KVA', 'bribox_category_id' => 7], // UPS
            ['bribox_id' => 'G3', 'type' => 'UPS 10-15 KVA', 'bribox_category_id' => 7], // UPS
            ['bribox_id' => 'G4', 'type' => 'UPS >15 KVA', 'bribox_category_id' => 7], // UPS
            ['bribox_id' => 'H1', 'type' => 'PANEL KK DAN TERAS', 'bribox_category_id' => 8], // PANEL
            ['bribox_id' => 'H2', 'type' => 'PANEL KANTOR UNIT', 'bribox_category_id' => 8], // PANEL
            ['bribox_id' => 'H3', 'type' => 'PANEL KCP', 'bribox_category_id' => 8], // PANEL
            ['bribox_id' => 'H4', 'type' => 'PANEL KC', 'bribox_category_id' => 8], // PANEL
            ['bribox_id' => 'H5', 'type' => 'PANEL KANWIL', 'bribox_category_id' => 8], // PANEL
            ['bribox_id' => 'I0', 'type' => 'RACK TAMBAHAN 20U', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I1', 'type' => 'ACCESS SWITCH 12 PORT', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I2', 'type' => 'ACCESS SWITCH 24 PORT', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I3', 'type' => 'ACCESS SWITCH 48 PORT', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I4', 'type' => 'DISTRIBUTION SWITCH 12 PORT', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I5', 'type' => 'ACCESS POINT', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I6', 'type' => 'RACK KANWIL,KC,KCP 20 U', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I7', 'type' => 'RACK KK,KU, TERAS 8 U', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I8', 'type' => 'RACK TAMBAHAN 8 U', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'I9', 'type' => 'RACK TAMBAHAN 12 U', 'bribox_category_id' => 9], // NETWORK
            ['bribox_id' => 'J1', 'type' => 'TITIK POWER', 'bribox_category_id' => 10], // INFRASTRUCTURE
            ['bribox_id' => 'J2', 'type' => 'TITIK LAN', 'bribox_category_id' => 10], // INFRASTRUCTURE
        ];

        foreach ($briboxes as $bribox) {
            Bribox::updateOrCreate(
                ['bribox_id' => $bribox['bribox_id']],
                $bribox
            );
        }
    }
}
