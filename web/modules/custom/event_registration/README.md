# Event Registration Module

A custom Drupal 11 module featuring an event registration form with integrated DEVEL/KINT debugging.

## Features

✅ **Complete Event Registration Form** with:
- First/Last Name fields
- Email validation
- Phone number input
- Event selection (radio buttons)
- Number of attendees
- Dietary restrictions (checkboxes)
- Comments textarea
- Terms & conditions checkbox

✅ **DEVEL/KINT Debugging**:
- Debug fieldset showing form state data
- Form values inspection using kint_print()
- Collapsible debug panel
- Logs full submission data when debugging is enabled

✅ **Form Validation**:
- Email format validation
- Phone number format validation
- Required field validation

## File Structure

```
event_registration/
├── event_registration.info.yml      # Module metadata
├── event_registration.module         # Module hooks
├── event_registration.routing.yml    # Route definition
├── src/Form/
│   └── EventRegistrationForm.php     # Form class with DEVEL integration
└── README.md                         # This file
```

## How to Use

### 1. Enable the Module
```bash
# Via Drush
ddev drush en event_registration

# Or via Admin UI
# Navigate to: /admin/modules
# Search for "Event Registration"
# Check the box and click Install
```

### 2. Access the Form
Visit: `https://drupal112-training.ddev.site/form/event-registration`

### 3. View Debug Information
- The debug section appears when DEVEL module is enabled
- It shows current form state values in a collapsible fieldset
- Use kint to inspect form structure and data

### 4. Check Logs
```bash
ddev drush log:watch

# OR view logs in Drupal:
# Navigate to: /admin/reports/dblog
# Filter by channel: "event_registration"
```

## Debugging with DEVEL/KINT

### In the Form
- A "🔍 Debug Information" section appears at the bottom when DEVEL is active
- Shows form state keys and values using kint_print()
- Toggle the fieldset to expand/collapse

### In the Browser Console
The module logs registrations to Drupal's logger:
```php
\Drupal::logger('event_registration')->info('...');
\Drupal::logger('event_registration')->debug('...');
```

### Using Kint Directly
Add this to any PHP file to dump variables:
```php
kint($variable);        // Display and stop execution
kint_print($variable);  // Return as string
```

## Form State Inspection

The `EventRegistrationForm::buildForm()` method includes:
```php
// Check current form values
$values = $form_state->getValues();

// Debug in form build
kint_print($values);

// Log with debugging
if ($this->isDevelEnabled()) {
  // Debug code runs only when devel is enabled
}
```

## Validation Rules

- **Email**: Must be valid format (uses filter_var with FILTER_VALIDATE_EMAIL)
- **Phone**: Optional, must contain only digits, +, -, (, ), and spaces if provided
- **First/Last Name**: Max 100 characters
- **Attendees**: Between 1 and 50

## Key Classes & Methods

### EventRegistrationForm
- `buildForm()` - Builds the form structure with debug section
- `validateForm()` - Validates email and phone
- `submitForm()` - Handles form submission and logging
- `resetForm()` - Clears the form
- `isDevelEnabled()` - Checks if DEVEL module is active

## Dependencies

- Drupal 11 core
- Optional: DEVEL module (for enhanced debugging)
- Optional: KINT module (for variable inspection)

## Notes

- The form uses Drupal's Form API
- All strings are translatable (use of t() function)
- DEVEL/KINT sections are conditionally rendered
- Form data is logged for audit purposes
- No data is persisted to database (use Entity API for that)
