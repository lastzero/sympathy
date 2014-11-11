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
required               | Field cannot be empty (if false, setDefinedValues() and setDefinedWritableValues() still throw an exception, if it does not exist at all)
optional               | setDefinedValues() and setDefinedWritableValues() don't throw an exception, if the field is missing in the input values (usefull for checkboxes or certain JavaScript frameworks, that do not submit any data for empty form elements e.g. AngularJS)
readonly               | User is not allowed to change the field
hidden                 | User can not see the field
default                | Default value
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
            'username': {
                type: 'string',
                caption: 'Username',
                required: true,
                min: 3,
                max: 15
            },
            'email': {
                type: 'email',
                caption: 'E-Mail',
                required: true
            },
            'gender': {
                type: 'string',
                caption: 'Gender',
                required: false,
                options: ['male', 'female'],
                optional: true
            },
            'birthday': {
                type: 'date',
                caption: 'Birthday',
                required: false
            },
            'password': {
                type: 'string',
                caption: 'Password',
                required: true,
                min: 5,
                max: 30
            },
            'password_again': {
                type: 'string',
                caption: 'Password confirmation',
                required: true,
                matches: 'password'
            },
            'continent': {
                type: 'string',
                caption: 'Region',
                required: true,
                options: ['north_america', 'south_america', 'europe', 'asia', 'australia']
            }
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
    
    public function putAction($id, Request $request) // Update
    {
        $this->user->find($id); // Find entity (throws exception, if not found)
        
        $this->form->setDefinedValues($this->user->getValues()); // Initialization
        
        $this->form->setDefinedWritableValues($request->request->all()); // Input values
        
        $this->form->validate(); // Validation

        if($this->form->isValid()) {
            $this->user->update($this->form->getValues()); // Update values
        } else {
            // Return first error, since HTTP isn't designed to return multiple errors at once
            throw new FormInvalidException($this->form->getFirstError());
        }

        return $this->user->getValues(); // Return updated entity values
    }
}
```

Form Validation vs Model Validation
-----------------------------------
The following visualization highlights the differences between client-side, input value (form) and model validation. In general, model validation operates on **trusted** data (internal system state) and should ideally be **repeatable** at any point in time while input validation explicitly operates **once** on data that comes from **untrusted** sources (depending on the use case and user privileges). This separation makes it possible to build reusable models, controllers and forms that can be coupled through dependency injection as shown in the example above. Think of input validation as **white list** validation ("Accept known good") and model validation as **black list** validation ("Reject known bad"). White list validation is more secure while black list validation prevents your model layer from being overly constraint to very specific use cases.

Invalid model data should always cause an **exception** to be thrown (otherwise the application can continue running without noticing the mistake) while invalid input values coming from external sources are **not** really unexpected, but rather common (think of a user registration form with the email or username already taken). Validation within a specific model may not be possible at all, if a set of input values must be validated together (because they depend on each other) but individual values are then stored in different models - at least it can create **additional dependencies** between models that would not be there otherwise up to the point that all models depend on each other. In short: The application may still work as expected, but the code is a mess.

From a theoretical standpoint, any complex system has more **internal state** than it exposes to the outside, thus it is never sufficient to use model validation only - except the model provides two sets of methods: some that are used internally and some that can be exposed to arbitrary input data from any source. Aside from side-effects such as limited user feedback (exception messages) and bloated model code, this approach may easily lead to serious security flaws. Malicious input data is a much higher threat to **multi-user** Web applications than to classical **single-user** desktop applications. Simple black list model validation may be fully sufficient for desktop applications, which are in full control of the user interface (view layer).

Client-side (JavaScript or HTML) form validation is always just a convenience feature and **not reliable**. However, you can (at least partly) **reuse existing server-side form validation** rules to perform client-side validation, since they can be easily converted to JSON (for JavaScript) or be passed to template rendering engines such as Twig or Smarty (for HTML). Reusing model layer validation rules in a similar fashion is at least difficult, if not impossible.

See also: https://www.owasp.org/index.php/Data_Validation#Where_to_include_business_rule_validation

![Differences between client-side, input value (form) and model validation](https://www.lucidchart.com/publicSegments/view/5461f867-ae1c-44a4-b565-6f780a00cf27/image.png)
