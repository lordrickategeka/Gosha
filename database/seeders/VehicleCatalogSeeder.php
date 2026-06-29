<?php

namespace Database\Seeders;

use App\Domains\Marketplace\Models\CatalogProduct;
use App\Domains\Marketplace\Models\PartCategory;
use App\Domains\Marketplace\Models\VehicleBrand;
use App\Domains\Marketplace\Models\VehicleModel;
use App\Domains\Marketplace\Models\VehicleVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Small canonical seed: a few brands/models/variants and a handful of verified products
 * wired to compatible vehicles, so the marketplace demos meaningfully out of the box.
 */
class VehicleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['Filters', 'Brakes', 'Engine', 'Suspension', 'Wash Supplies'])
            ->mapWithKeys(fn ($name) => [
                $name => PartCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]),
            ]);

        $tree = [
            'Toyota' => [
                'Corolla' => [['1.8 VVT-i', 2014, 2019], ['1.6 VVT-i', 2008, 2013]],
                'Hilux'   => [['2.8 GD-6', 2016, 2024]],
            ],
            'Nissan' => [
                'X-Trail' => [['2.0 dCi', 2014, 2021]],
            ],
            'Mitsubishi' => [
                'Pajero' => [['3.2 DI-D', 2007, 2020]],
            ],
        ];

        $variants = collect();
        foreach ($tree as $brandName => $models) {
            $brand = VehicleBrand::firstOrCreate(['slug' => Str::slug($brandName)], ['name' => $brandName]);
            foreach ($models as $modelName => $vs) {
                $model = VehicleModel::firstOrCreate(
                    ['vehicle_brand_id' => $brand->id, 'slug' => Str::slug($modelName)],
                    ['name' => $modelName]
                );
                foreach ($vs as [$vName, $yFrom, $yTo]) {
                    $variants->push(VehicleVariant::firstOrCreate(
                        ['vehicle_model_id' => $model->id, 'name' => $vName],
                        ['year_from' => $yFrom, 'year_to' => $yTo]
                    ));
                }
            }
        }

        $products = [
            ['Bosch', '0986452041', 'Oil Filter', 'Filters', ['Corolla', 'X-Trail']],
            ['Brembo', 'P83024', 'Front Brake Pads', 'Brakes', ['Corolla', 'Hilux']],
            ['NGK', 'BKR6E', 'Spark Plug', 'Engine', ['Corolla']],
            ['Monroe', 'G8246', 'Front Shock Absorber', 'Suspension', ['Hilux', 'Pajero']],
            ['Meguiars', 'G7101', 'Car Wash Shampoo 1.89L', 'Wash Supplies', []],
        ];

        foreach ($products as [$brand, $pn, $name, $cat, $fitsModels]) {
            $product = CatalogProduct::firstOrCreate(
                ['brand' => $brand, 'part_number' => $pn],
                [
                    'name' => $name,
                    'slug' => Str::slug("$brand $name $pn"),
                    'category_id' => $categories[$cat]->id,
                    'unit_of_measure' => $cat === 'Wash Supplies' ? 'litre' : 'unit',
                    'is_verified' => true,
                    'is_active' => true,
                ]
            );

            $fitIds = $variants
                ->filter(fn ($v) => in_array($v->model->name, $fitsModels, true))
                ->pluck('id')
                ->all();

            if ($fitIds) {
                $product->compatibleVariants()->syncWithoutDetaching($fitIds);
            }
        }
    }
}
