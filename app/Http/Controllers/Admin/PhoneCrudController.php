<?php

namespace App\Http\Controllers\Admin;

use App\Models\BgImage;
use App\Models\Phone;
use App\Jobs\DeleteItlJob;
use Backpack\CRUD\CrudPanel;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use App\Jobs\PushPhoneBackgroundImageJob;
use App\Http\Requests\PhoneRequest as StoreRequest;
use App\Http\Requests\PhoneRequest as UpdateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation

/**
 * Class PhoneCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class PhoneCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Phone');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/phone');
        $this->crud->setEntityNameStrings('phone', 'phones');
        $this->crud->orderBy('device_pool', 'ASC');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        $this->crud->addColumns([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name' => 'description',
                'type' => 'text',
                'label' => 'Description',
            ],
            [
                'name' => 'status',
                'type' => 'text',
                'label' => 'Status',
            ],
            [
                'name' => 'model',
                'type' => 'text',
                'label' => 'Model',
            ],
            [
                'name' => 'device_pool',
                'type' => 'text',
                'label' => 'Device Pool',
            ],
            [
                'name' => 'owner_user_name',
                'type' => 'text',
                'label' => 'Owner',
            ],
            [
                'name' => 'Call Manager',
                'type' => 'select',
                'entity' => 'ucm',
                'attribute' => 'name',
                'label' => 'Ucm',
                'model' => 'App\Models\Ucm',
            ],
        ]);

        $this->crud->removeAllButtons();
        $this->crud->enableDetailsRow();
        $this->crud->allowAccess('details_row');

        // Custom Buttons
        $this->crud->addButtonFromView('line', 'phone_delete_itl', 'phone_delete_itl', 'beginning');
        $this->crud->addButtonFromView('line', 'phone_push_background', 'phone_push_background', 'beginning');
        $this->crud->addButtonFromModelFunction('top', 'export_phones', 'exportPhones', 'beginning');
        $this->crud->addButtonFromModelFunction('top', 'bulk_delete_itl', 'bulkDeleteItl', 'end');

        // add asterisk for fields that are required in PhoneRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store(StoreRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    /**
     * @param Phone $phone
     * @return RedirectResponse
     */
    public function deleteItl(Phone $phone)
    {
        Log::info("PhoneCrudController@deleteItl: Received Delete ITL request for {$phone->name}");
        DeleteItlJob::dispatch($phone, backpack_user()->email);

        Alert::success("Delete ITL Submitted for {$phone->name}!")->flash();
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function bulkDeleteItl(Request $request)
    {
        Log::info("PhoneCrudController@bulkDeleteItl: Received Bulk Delete ITL request");
        $file = $request->file('bulkItlDeleteInputFile');

        if (!in_array($file->getClientMimeType(), ['text/plain', 'text/csv'])) {
            Log::error("PhoneCrudController@bulkDeleteItl: Invalid File Type", [
                $file->getClientMimeType()
            ]);
            Alert::error("Invalid File Type.  Please use .txt or .csv")->flash();
            return back();
        }

        Log::info("PhoneCrudController@bulkDeleteItl: File validation passed.  Opening file for reading");
        $csvFile = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        Log::info("PhoneCrudController@bulkDeleteItl: File opened.  Iterating devices");
        foreach ($csvFile as $row) {
            $phone = Phone::where('name', $row)->first();
            if (!$phone) {
                Log::error('PhoneCrudController@bulkDeleteItl: Phone name was not found in local DB', [
                    $row
                ]);
                continue;
            }
            Log::info("PhoneCrudController@bulkDeleteItl: Phone name was found in local DB.  Creating DeleteItlJob", [
                $phone->name
            ]);
            DeleteItlJob::dispatch($phone, backpack_user()->email);
        }

        Alert::success("Bulk Delete ITL Submitted!")->flash();
        return back();
    }

    public function pushBackground(Request $request)
    {
        Log::info("PhoneCrudController@pushBackground: Received pushBackground request");

        [$phone, $image] = [$request->phone, $request->image];
        Log::info("PhoneCrudController@pushBackground: Set POST params", [
            $phone, $image
        ]);

        Log::info("PhoneCrudController@pushBackground: Dispatching PushPhoneBackgroundImageJob");
        PushPhoneBackgroundImageJob::dispatch(Phone::find($phone), backpack_user()->email, BgImage::find($image)->image);

        Alert::success("Pushing new background image!")->flash();
        return back();
    }

    /**
     * Used with AJAX in the list view (datatables) to show extra information about that row that didn't fit in the table.
     * It defaults to showing some dummy text.
     *
     * It's enabled by:
     * - setting: $crud->details_row = true;
     * - adding the details route for the entity; ex: Route::get('page/{id}/details', 'PageCrudController@showDetailsRow');
     * - adding a view with the following name to change what the row actually contains: app/resources/views/vendor/backpack/crud/details_row.blade.php
     */
    public function showDetailsRow($id)
    {
        $this->crud->hasAccessOrFail('details_row');

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('crud::phones.details_row', $this->data);
    }
}
