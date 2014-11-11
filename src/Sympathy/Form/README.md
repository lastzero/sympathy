Sympathy Form validates user input of any origin (POST data, CLI or SOAP/REST)
==============================================================================

The form classes return localized validation messages and pass on the form definition
to controllers that render the forms to HTML using view templates and interact with
models (see \Sympathy\Model).

A major advantage of this modular approach is that developers can use unit testing to instantly
find bugs and tune the validation rules without the need for an HTML front-end and manual user input.

Form classes can inherit their definitions from each other. If needed, the validation behavior
can be changed using standard object-oriented methodologies (e.g. overwriting or extending
the parent methods).


Form field properties
---------------------

Property               | Description
---------------------- | ---------------------------------------------------------------------------------------------------
caption                | Field title (used for form rendering and in validation messages)
type                   | Data type: int, numeric, scalar, list, bool, string, email, ip, url, date, datetime, time and switch
type_params            | Optional parameters for data type validation
options                | Array of possible values for the field (for select lists or radio button groups)
min                    | Minimum value for numbers/dates, length for strings or number of elements for lists
max                    | Maximum value for numbers/dates, length for strings or number of elements for lists
required               | Field cannot be empty
readonly               | User is not allowed to change the field
hidden                 | User can not see the field
default                | Default value
optional               | setDefinedValues() and setDefinedWritableValues() don't throw an exception, if the field is missing in the input values (usefull for checkboxes or certain JavaScript frameworks, that do not submit any data for empty form elements e.g. AngularJS)
regex                  | Regular expression to match against
matches                | Field value must match another form field (e.g. for password or email validation). Property can be prefixed with "!" to state that the fields must be different.
depends                | Field is required, if the given form field is not empty
depends_value          | Field is required, if the field defined in "depends" has this value
depends_value_empty    | Field is required, if the field defined in "depends" is empty
depends_first_option   | Field is required, if the field defined in "depends" has the first value (see "options")
depends_last_option    | Field is required, if the field defined in "depends" has the last value (see "options")
page                   | Page number for multi-page forms

Example
-------
```
<?php

class UserForm extends Form {
    protected function init(array $params = array())
    {
        $definition = array(
            'firstname' => array('caption' => 'First Name', 'type' => 'string'),
            'lastname' => array('caption' => 'Last Name', 'type' => 'string'),
            'email' => array('caption' => 'E-Mail', 'type' => 'email'),
            'team_id' => array('caption' => 'Team ID', 'type' => 'int'),
            'admin' => array('caption' => 'Admin', 'type' => 'bool', 'optional' => true),
            'disabled' => array('caption' => 'Deactivated', 'type' => 'bool', 'optional' => true)
        );

        $this->setDefinition($definition);
    }
}
```

Validation in REST Controller action context
--------------------------------------------
```
class UserController
{
    protected $user;
    protected $form;

    public function __construct(User $user, UserForm $form)
    {
        $this->user = $user;
        $this->form = $form;
    }
    
    public function putAction($id, Request $request)
    {
        $this->user->find($id);
        $this->form->setDefinedWritableValues($request->request->all())->validate();

        if($this->form->hasErrors()) {
            throw new FormInvalidException($this->form->getFirstError());
        } else {
            $this->user->update($this->form->getValues());
        }

        return $this->user->getValues();
    }
}
```

Form Validation vs Model Validation
-----------------------------------
The following visualization highlights the differences between client-side, input value (form) and model validation. In general, model validation works on trusted data (internal system state) while input validation explicitly operates on data that comes from external sources (depending on the use case and user privileges). A complex system always has more internal state than it exposes to the outside, thus it is never sufficient to use model validation only (exept the model API provides two sets of methods - some that are used internally and some that are exposed as public API; that makes model code more difficult to manage and mistakes may easily lead to security flaws).

![Differences between client-side, input value (form) and model validation](https://www.lucidchart.com/publicSegments/view/5461c6ec-2c64-46b2-a1f1-19aa0a00cf27/image.png)
