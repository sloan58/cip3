<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Laracsv\Export;
use App\Models\Phone;
use Backpack\CRUD\CrudPanel;
use League\Csv\CannotInsertRecord;
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

        $this->crud->addButtonFromModelFunction('top', 'export_phones', 'exportPhones', 'beginning');

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

    /**
     * Export phone details
     *
     * @throws CannotInsertRecord
     */
    public function export()
    {
        $csvExporter = new Export();
        $phones = Phone::get();

        // Register the hook before building
        $csvExporter->beforeEach(function ($phone) {
            $phone->Status = $phone->realtime_data[0]['Status'];
            $phone->StatusReason = $phone->realtime_data[0]['StatusReason'];
            $phone->cip3_ucm = $phone->ucm->name;
            $phone->UcmNode = $phone->realtime_data[0]['UcmNode'];
            $phone->Protocol = $phone->realtime_data[0]['Protocol'];
            $phone->IPAddress = $phone->realtime_data[0]['IPAddress'];
            $phone->NumOfLines = $phone->realtime_data[0]['NumOfLines'];
            $phone->ActiveLoadID = $phone->realtime_data[0]['ActiveLoadID'];
            $phone->InactiveLoadID = $phone->realtime_data[0]['InactiveLoadID'];
            if($phone->realtime_data[0]['UCMTimestamp']) {
                $phone->UCMTimestamp = Carbon::createFromTimestamp($phone->realtime_data[0]['UCMTimestamp'])
                    ->toDateTimeString();
            }
            if($phone->realtime_data[0]['CIP3Timestamp']) {
                $phone->CIP3Timestamp = Carbon::createFromTimestamp($phone->realtime_data[0]['CIP3Timestamp'])
                    ->toDateTimeString();
            }
        });

        $csvExporter->build($phones, [
            'name',
            'description',
            'model',
            'device_pool',
            'cip3_ucm',
            'Status',
            'StatusReason',
            'UcmNode',
            'IPAddress',
            'Protocol',
            'NumOfLines',
            'ActiveLoadID',
            'InactiveLoadID',
            'UCMTimestamp',
            'CIP3Timestamp',
        ]);
        $csvExporter->download();
    }
}
