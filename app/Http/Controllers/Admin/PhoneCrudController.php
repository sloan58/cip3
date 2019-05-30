<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\PhoneRequest as StoreRequest;
use App\Http\Requests\PhoneRequest as UpdateRequest;
use Backpack\CRUD\CrudPanel;

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
}
