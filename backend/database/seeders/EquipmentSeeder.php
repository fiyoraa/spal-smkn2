<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Services\QrCodeService;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $equipmentsData = [
            // LAPTOP (20 items)
            ['name' => 'Laptop Lenovo ThinkPad L14 Gen 1', 'category' => 'laptop'],
            ['name' => 'Laptop Lenovo ThinkPad L14 Gen 2', 'category' => 'laptop'],
            ['name' => 'Laptop Lenovo ThinkPad X1 Carbon', 'category' => 'laptop'],
            ['name' => 'Laptop Lenovo ThinkPad E14', 'category' => 'laptop'],
            ['name' => 'Laptop ASUS ExpertBook B1400', 'category' => 'laptop'],
            ['name' => 'Laptop ASUS ExpertBook B9450', 'category' => 'laptop'],
            ['name' => 'Laptop Dell Latitude 3420', 'category' => 'laptop'],
            ['name' => 'Laptop Dell Latitude 5420', 'category' => 'laptop'],
            ['name' => 'Laptop HP ProBook 440 G8', 'category' => 'laptop'],
            ['name' => 'Laptop HP EliteBook 840 G7', 'category' => 'laptop'],
            ['name' => 'Switch Cisco Catalyst 2960 24-Port', 'category' => 'laptop'],
            ['name' => 'Switch Cisco Catalyst 2960 48-Port', 'category' => 'laptop'],
            ['name' => 'Router Mikrotik RB951Ui-2HnD', 'category' => 'laptop'],
            ['name' => 'Router Mikrotik CCR1009-7G-1C-1S+', 'category' => 'laptop'],
            ['name' => 'Access Point Ubiquiti UniFi AP AC LR', 'category' => 'laptop'],
            ['name' => 'Access Point Cisco Aironet 1830', 'category' => 'laptop'],
            ['name' => 'Server Dell PowerEdge R740', 'category' => 'laptop'],
            ['name' => 'Server HP ProLiant DL360 Gen10', 'category' => 'laptop'],
            ['name' => 'NAS Synology DiskStation DS920+', 'category' => 'laptop'],
            ['name' => 'Mini PC Intel NUC 11 Pro', 'category' => 'laptop'],

            // PROYEKTOR (12 items)
            ['name' => 'Proyektor Epson EB-X06 XGA', 'category' => 'proyektor'],
            ['name' => 'Proyektor Epson EB-X51 XGA', 'category' => 'proyektor'],
            ['name' => 'Proyektor Epson EB-FH06 Full HD', 'category' => 'proyektor'],
            ['name' => 'Proyektor BenQ MX550 XGA', 'category' => 'proyektor'],
            ['name' => 'Proyektor BenQ MH560 Full HD', 'category' => 'proyektor'],
            ['name' => 'Proyektor Optoma SA500 SVGA', 'category' => 'proyektor'],
            ['name' => 'Proyektor ViewSonic PA503XE', 'category' => 'proyektor'],
            ['name' => 'Monitor LED LG 24MK600 24 Inch', 'category' => 'proyektor'],
            ['name' => 'Monitor LED Samsung T350 24 Inch', 'category' => 'proyektor'],
            ['name' => 'Monitor LED ASUS VP229HE 21.5 Inch', 'category' => 'proyektor'],
            ['name' => 'Smart TV Xiaomi Mi TV 4 43 Inch', 'category' => 'proyektor'],
            ['name' => 'Wireless Display Dongle AnyCast', 'category' => 'proyektor'],

            // KAMERA (11 items)
            ['name' => 'Kamera Canon EOS 1500D Kit 18-55mm', 'category' => 'kamera'],
            ['name' => 'Kamera Canon EOS 200D II Kit', 'category' => 'kamera'],
            ['name' => 'Kamera Sony Alpha A6400 Mirrorless', 'category' => 'kamera'],
            ['name' => 'Kamera Sony Alpha A6000 Mirrorless', 'category' => 'kamera'],
            ['name' => 'Kamera Fujifilm X-T200 Mirrorless', 'category' => 'kamera'],
            ['name' => 'Kamera Nikon D3500 DSLR Kit', 'category' => 'kamera'],
            ['name' => 'Webcam Logitech C922 Pro Stream', 'category' => 'kamera'],
            ['name' => 'Webcam Logitech Brio 4K Ultra HD', 'category' => 'kamera'],
            ['name' => 'Handycam Sony HDR-CX405 HD', 'category' => 'kamera'],
            ['name' => 'Capture Card Elgato Cam Link 4K', 'category' => 'kamera'],
            ['name' => 'Gimbal Stabilizer DJI Ronin-SC', 'category' => 'kamera'],

            // TRIPOD (12 items)
            ['name' => 'Tripod Takara Eco-196 Video', 'category' => 'tripod'],
            ['name' => 'Tripod Manfrotto Compact Light', 'category' => 'tripod'],
            ['name' => 'Tripod Somita ST-3520 Pro', 'category' => 'tripod'],
            ['name' => 'Light Stand Takara Spirit-3', 'category' => 'tripod'],
            ['name' => 'Tang Crimping RJ45 AMP original', 'category' => 'tripod'],
            ['name' => 'Tang Crimping RJ45 HT-568R', 'category' => 'tripod'],
            ['name' => 'Lan Cable Tester Digital NF-468', 'category' => 'tripod'],
            ['name' => 'Obeng Set Precision Jakemy JM-8183', 'category' => 'tripod'],
            ['name' => 'Obeng Set Toolkit Krisbow 21-in-1', 'category' => 'tripod'],
            ['name' => 'Solder Listrik Weller WE1010 ESD', 'category' => 'tripod'],
            ['name' => 'Digital Multimeter Sanwa CD800a', 'category' => 'tripod'],
            ['name' => 'Blower Solder Uap Gordak 850A', 'category' => 'tripod'],
        ];

        $locations = ['Lemari Lab A', 'Lemari Lab B', 'Ruang Penyimpanan', 'Rak Praktikum 1', 'Rak Praktikum 2'];
        $qrService = new QrCodeService();

        foreach ($equipmentsData as $item) {
            // 1. Save with a temporary unique code first to satisfy DB constraints
            $equipment = Equipment::create([
                'code' => 'TEMP-' . microtime(true) . '-' . rand(1000, 9999),
                'name' => $item['name'],
                'category' => $item['category'],
                'status' => 'Tersedia',
                'location' => $locations[array_rand($locations)],
            ]);

            // 2. Generate standard ID format and base64 QR code image using our service
            $code = $qrService->generateCode($equipment->category, $equipment->id);
            $qrImage = $qrService->generateQrImage($code);

            // 3. Update the equipment model with actual values
            $equipment->update([
                'code' => $code,
                'qr_code' => $qrImage
            ]);
        }
    }
}
