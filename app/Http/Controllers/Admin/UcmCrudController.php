<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\SyncUcmJob;
use App\Jobs\UpdateRealtimeDataJob;
use SoapFault;
use App\Models\Ucm;
use App\ApiClients\AxlSoap;
use Backpack\CRUD\CrudPanel;
use App\ApiClients\RisPortSoap;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\UcmRequest as StoreRequest;
use App\Http\Requests\UcmRequest as UpdateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation

/**
 * Class UcmCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class UcmCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Ucm');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/ucm');
        $this->crud->setEntityNameStrings('ucm', 'ucms');

        // Custom Buttons
        $this->crud->addButtonFromView('line', 'sync', 'ucm_sync', 'beginning');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

//        $this->crud->setColumns([
//            'Name',
//            'IP Address',
//            'Username',
//            'Version'
//        ]);

        $this->crud->addColumns([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name' => 'ip_address',
                'type' => 'text',
                'label' => 'IP Address',
            ],
            [
                'name' => 'version',
                'type' => 'text',
                'label' => 'API Version',
            ],
        ]);

        $this->crud->addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name'
            ],
            [
                'name' => 'ip_address',
                'type' => 'text',
                'label' => 'IP Address'
            ],
            [
                'name' => 'username',
                'type' => 'text',
                'label' => 'API Username'
            ],
            [
                'name' => 'password',
                'type' => 'password',
                'label' => 'API Password'
            ],
            [
                'name' => 'version',
                'type' => 'select_from_array',
                'label' => 'API Version',
                'options' => array_combine(
                    Ucm::getApiVersions(),
                    Ucm::getApiVersions()
                )
            ],
            [
                'name' => 'timezone',
                'type' => 'select_from_array',
                'label' => 'Timezone',
                'options' => array_combine(
                    timezone_identifiers_list(),
                    timezone_identifiers_list()
                )
            ],
            [
                'name' => 'sync_at',
                'type' => 'time',
                'label' => 'Perform Sync At'
            ],
            [
                'name' => 'sync_schedule_enabled',
                'type' => 'checkbox',
                'label' => 'Daily Sync Enabled',
                'default' => 1
            ],
            [
                'name' => 'verify_peer',
                'type' => 'checkbox',
                'label' => 'Validate Certificate'
            ]
        ]);

        $this->crud->enableDetailsRow();
        $this->crud->allowAccess('details_row');
        
        // add asterisk for fields that are required in UcmRequest
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
        // Encrypt password if specified.
        if (!$request->input('password')) {
            $request->request->remove('password');
        }

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
        return view('crud::ucms.details_row', $this->data);
    }

    /**
     * Sync a UCM Server on-demand
     *
     * @param Ucm $ucm
     * @return RedirectResponse
     */
    public function sync(Ucm $ucm)
    {
        $ucm->sync_in_progress = true;
        $ucm->save();

        SyncUcmJob::dispatch($ucm);
        Alert::success("UCM Sync Initiated for UCM {$ucm->name}!")->flash();
        return back();
    }

    /**
     * Sync a UCM Server on-demand
     *
     * @param Ucm $ucm
     * @return RedirectResponse
     */
    public function updateRealtime(Ucm $ucm)
    {
        $ucm->sync_in_progress = true;
        $ucm->save();

        UpdateRealtimeDataJob::dispatch($ucm);
        Alert::success("UCM Realtime Sync Initiated for UCM {$ucm->name}!")->flash();
        return back();
    }
}
