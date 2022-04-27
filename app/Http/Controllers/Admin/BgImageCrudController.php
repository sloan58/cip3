<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\CrudPanel;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\BgImageRequest as StoreRequest;
use App\Http\Requests\BgImageRequest as UpdateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation

/**
 * Class BgImageCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class BgImageCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\BgImage');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/bgimage');
        $this->crud->setEntityNameStrings('Background Image', 'Background Images');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        $supportedModels = [
            '95x34'   => 'Cisco 7911',
            '320x212' => 'Cisco 7971 | Cisco 7970 | Cisco 7965 | Cisco 7945',
            '320x196' => 'Cisco 7962 | Cisco 7961 | Cisco 7942 | Cisco 7941',
            '320x216' => 'Cisco 7975',
            '640x480' => 'Cisco 9971 | Cisco 9951',
            '800x400' => 'Cisco 8845 | Cisco 8851 | Cisco 8861'
        ];

        $this->crud->addColumns([
            [
                'name' => 'image',
                'label' => "Image Preview",
                'type' => 'view',
                'view' => 'vendor.backpack.crud.columns.bgImages.image',
            ],
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name' => 'dimensions',
                'type' => 'select_from_array',
                'label' => 'Model',
                'options' => $supportedModels
            ],
            [
                'name' => 'device_pools', // The db column name
                'label' => "Device Pools", // Table column heading
                'type' => 'array'
            ],
        ]);

        $this->crud->addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name' => 'dimensions',
                'type' => 'select_from_array',
                'label' => 'Model',
                'options' => $supportedModels
            ],
            [
                'name' => 'full_image',
                'label' => 'Full Image',
                'type' => 'upload',
                'upload' => true,
            ],
            [
                'name' => 'thumbnail_image',
                'label' => 'Thumbnail Image',
                'type' => 'upload',
                'upload' => true,
            ],
            [
                'name' => 'device_pools',
                'label' => "Device Pools",
                'type' => 'select2_from_array',
                'options' => \App\Models\BgImage::availableDevicePools(),
                'allows_null' => false,
                'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
            ],
        ]);

        // add asterisk for fields that are required in BgImageRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store(StoreRequest $request)
    {
        Log::info("BgImageCrudController@store: Received new BgImage request");

        $dimensions = $request->dimensions;
        Log::info("BgImageCrudController@store: Set dimensions to $dimensions");

        $fullImageName = str_replace(' ', '', $request->file('full_image')->getClientOriginalName());
        Log::info("BgImageCrudController@store: Set full image name to $fullImageName");

        $outFile = $request->file('full_image')->storeAs("backgrounds/$dimensions", $fullImageName, 'public');
        Log::info("BgImageCrudController@store: $fullImageName stored to $outFile");

        $thumbnailImageName = sprintf(
            "%s_thumb.png",
            basename($fullImageName, '.png')
        );
        Log::info("BgImageCrudController@store: Set thumbnail image name to $thumbnailImageName");

        $outFile = $request->file('thumbnail_image')->storeAs("backgrounds/$dimensions", $thumbnailImageName, 'public');
        Log::info("BgImageCrudController@store: $thumbnailImageName stored to $outFile");

        $request->request->add(['image' => $fullImageName]);
        Log::info("BgImageCrudController@store: Added `image` request param");

        Log::info("BgImageCrudController@store: Adding Device Pools to device_pools");
        if (in_array('All', $request->get('device_pools'))) {
            $request->request->remove('device_pools');
            $request->request->add(['device_pools' => ['All']]);
        }

        Log::info("BgImageCrudController@store: Handing off to Backpack to store model");
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function update(UpdateRequest $request)
    {
        // TODO: Fix issue with moving files when one exists with the same name

        if ($request->has('full_image')) {
            Log::info("BgImageCrudController@update: Request is updating image files");

            $dimensions = $request->dimensions;
            Log::info("BgImageCrudController@update: Set dimensions to $dimensions");

            $fullImageName = str_replace(' ', '', $request->file('full_image')->getClientOriginalName());
            Log::info("BgImageCrudController@update: Set full image name to $fullImageName");

            $outFile = $request->file('full_image')->storeAs("backgrounds/$dimensions", $fullImageName, 'public');
            Log::info("BgImageCrudController@update: $fullImageName stored to $outFile");

            $thumbnailImageName = sprintf(
                "%s_thumb.png",
                basename($fullImageName, '.png')
            );
            Log::info("BgImageCrudController@update: Set thumbnail image name to $thumbnailImageName");

            $outFile = $request->file('thumbnail_image')->storeAs("backgrounds/$dimensions", $thumbnailImageName, 'public');
            Log::info("BgImageCrudController@update: $thumbnailImageName stored to $outFile");

            $request->request->add(['image' => $fullImageName]);
            Log::info("BgImageCrudController@update: Added `image` request param");

            Log::info("BgImageCrudController@update: Handing off to Backpack to update model");
        }

        Log::info("BgImageCrudController@update: Adding Device Pools to device_pools");
        if (in_array('All', $request->get('device_pools'))) {
            $request->request->remove('device_pools');
            $request->request->add(['device_pools' => ['All']]);
        }

        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }
}
