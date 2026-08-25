<?php

namespace Database\Seeders;

use App\Models\DigitaxUnitCode;
use Illuminate\Database\Seeder;

/**
 * Official DigiTax/KRA eTIMS package_unit_code and quantity_unit_code
 * tables, sourced from https://ke.docs.digitax.tech/docs/item-attributes-items
 */
class DigitaxUnitCodeSeeder extends Seeder
{
    public function run(): void
    {
        $packageUnits = [
            'AM' => 'Ampoule',
            'BA' => 'Barrel',
            'BC' => 'Bottlecrate',
            'BE' => 'Bundle',
            'BF' => 'Balloon, non-protected',
            'BG' => 'Bag',
            'BJ' => 'Bucket',
            'BK' => 'Basket',
            'BL' => 'Bale',
            'BQ' => 'Bottle, protected cylindrical',
            'BR' => 'Bar',
            'BV' => 'Bottle, bulbous',
            'BZ' => 'Bag',
            'CA' => 'Can',
            'CH' => 'Chest',
            'CJ' => 'Coffin',
            'CL' => 'Coil',
            'CR' => 'Wooden Box, Wooden Case',
            'CS' => 'Cassette',
            'CT' => 'Carton',
            'CTN' => 'Container',
            'CY' => 'Cylinder',
            'DR' => 'Drum',
            'GT' => 'Extra Countable Item',
            'HH' => 'Hand Baggage',
            'IZ' => 'Ingots',
            'JR' => 'Jar',
            'JU' => 'Jug',
            'JY' => 'Jerry Can Cylindrical',
            'KZ' => 'Canester',
            'LZ' => 'Logs, in bundle/bunch/truss',
            'ML' => 'Cigarette Mills',
            'NT' => 'Net',
            'OU' => 'Non-Exterior Packaging Unit',
            'PD' => 'Poddon',
            'PG' => 'Plate',
            'PI' => 'Pipe',
            'PO' => 'Pilot',
            'PU' => 'Traypack',
            'RL' => 'Reel',
            'RO' => 'Roll',
            'RZ' => 'Rods, in bundle/bunch/truss',
            'SK' => 'Skeletoncase',
            'TN' => 'Tan (1 Tan = 20 bags)',
            'TY' => 'Tank, cylindrical',
            'VG' => 'Bulk, gas (at 1031 mbar 15°C)',
            'VL' => 'Bulk, liquid (at normal temperature/pressure)',
            'VO' => 'Bulk, solid, large particles (nodules)',
            'VQ' => 'Bulk, gas (liquefied at abnormal temperature/pressure)',
            'VR' => 'Bulk, solid, granular particles (grains)',
            'VT' => 'Extra Bulk Item',
            'VY' => 'Bulk, fine particles (powder)',
        ];

        $quantityUnits = [
            '4B' => 'Pair',
            'AV' => 'Cap',
            'BA' => 'Barrel',
            'BE' => 'Bundle',
            'BG' => 'Bag',
            'BL' => 'Block',
            'BLL' => 'Barrel (petroleum) (158.987 dm3)',
            'BX' => 'Box',
            'CA' => 'Can',
            'CEL' => 'Cell',
            'CMT' => 'Centimetre',
            'CR' => 'Carat',
            'DR' => 'Drum',
            'DZ' => 'Dozen',
            'GLL' => 'Gallon',
            'GRM' => 'Gram',
            'GRO' => 'Gross',
            'KG' => 'Kilo-Gramme',
            'KTM' => 'Kilometre',
            'KWT' => 'Kilowatt',
            'L' => 'Litre',
            'LBR' => 'Pound',
            'LK' => 'Link',
            'LTR' => 'Litre',
            'M' => 'Metre',
            'M2' => 'Square Metre',
            'M3' => 'Cubic Metre',
            'MGM' => 'Milligram',
            'MTR' => 'Metre',
            'MWT' => 'Megawatt hour (1000 kW·h)',
            'NO' => 'Number',
            'NX' => 'Part per thousand',
            'PA' => 'Packet',
            'PG' => 'Plate',
            'PR' => 'Pair',
            'RL' => 'Reel',
            'RO' => 'Roll',
            'SET' => 'Set',
            'ST' => 'Sheet',
            'TNE' => 'Tonne (metric ton)',
            'TU' => 'Tube',
            'U' => 'Pieces/Item (Number)',
            'YRD' => 'Yard',
        ];

        foreach ($packageUnits as $code => $label) {
            DigitaxUnitCode::updateOrCreate(
                ['type' => 'package', 'code' => $code],
                ['label' => $label]
            );
        }

        foreach ($quantityUnits as $code => $label) {
            DigitaxUnitCode::updateOrCreate(
                ['type' => 'quantity', 'code' => $code],
                ['label' => $label]
            );
        }
    }
}
