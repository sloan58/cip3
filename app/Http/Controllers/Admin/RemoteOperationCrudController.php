<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\EraserRequest as StoreRequest;
use App\Http\Requests\EraserRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

/**
 * Class RemoteOperationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class RemoteOperationCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\RemoteOperation');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/remote-operation');
        $this->crud->setEntityNameStrings('remote-operation', 'remote-operation');

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->setFromDb();

        // Remove buttons.  This CRUD is read only
        $this->crud->removeAllButtons();

        // add asterisk for fields that are required in EraserRequest
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
}
