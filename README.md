# RenderEase

**RenderEase** is a lightweight PHP templating engine designed for simplicity and efficiency. It allows developers to easily manage variables, handle errors, and render templates without the overhead of larger frameworks. Whether you're building a simple website or a complex application, RenderEase provides a user-friendly interface that streamlines the templating process.

---

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
  - [Setting Variables](#setting-variables)
  - [Rendering Templates](#rendering-templates)
  - [Error Handling](#error-handling)
- [Advanced Usage](#advanced-usage)
  - [Clearing Variables](#clearing-variables)
  - [Multiple Variables](#multiple-variables)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- **Simple Variable Management**: Easily set, retrieve, and clear variables for your templates.
- **Error Handling**: Customizable error templates to handle rendering errors gracefully.
- **Flexible Template Rendering**: Include other templates for modular structures.
- **Easy Integration**: Can be quickly set up in existing PHP projects.

---

## Installation

To install RenderEase, clone the repository into your project directory:

```bash
git clone https://github.com/almhdy24/renderease.git
```

Include the `RenderEase.php` file in your project.

```php
require 'path/to/renderease/RenderEase.php';
```

---

## Basic Usage

### Setting Variables

You can set single or multiple variables for your templates using the `set` and `setMultiple` methods.

```php
$renderer = new Almhdy\RenderEase\RenderEase();

// Set a single variable
$renderer->set('title', 'Welcome Page');

// Set multiple variables
$renderer->setMultiple([
    'username' => 'John',
    'message' => 'This is your dashboard.'
]);
```

### Rendering Templates

Render templates by calling the `render` method and passing the name of your template file (excluding the `.php` extension).

```php
$output = $renderer->render('welcome');
echo $output;
```

Make sure your template files are in the correct directory as specified in your RenderEase configuration.

### Error Handling

You can customize how errors are handled in the template rendering process. To set an error template, use the `setErrorTemplate` method.

```php
$renderer->setErrorTemplate('error_template');
```

If an error occurs while rendering, it will automatically display the specified error template.

---

## Advanced Usage

### Clearing Variables

To clear all variables you’ve set, use the `clear` method:

```php
$renderer->clear();
```

### Multiple Variables

Easily set multiple variables at once with the `setMultiple` method without the hassle of setting them individually.

```php
$renderer->setMultiple([
    'firstName' => 'Alice',
    'lastName' => 'Smith',
    'age' => 28
]);
```

---

## Examples

### Example Template (`welcome.php`)

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title; ?></title>
</head>
<body>
    <h1>Welcome, <?php echo $username; ?>!</h1>
    <p><?php echo $message; ?></p>
</body>
</html>
```

### Full Example Usage

```php
require 'path/to/renderease/RenderEase.php';

// Initialize the renderer
$renderer = new Almhdy\RenderEase\RenderEase();

// Set variables
$renderer->set('title', 'Welcome Page');
$renderer->setMultiple([
    'username' => 'John',
    'message' => 'This is your dashboard.'
]);

// Render template
try {
    $output = $renderer->render('welcome');
    echo $output;
} catch (Exception $e) {
    // Handle exception (optional)
    echo "An error occurred: " . $e->getMessage();
}
```

---

## Contributing

We welcome contributions to enhance RenderEase! If you have suggestions, bug fixes, or features to propose, please open an issue or make a pull request.

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/YourFeature`).
3. Commit your changes (`git commit -m 'Add some feature'`).
4. Push to the branch (`git push origin feature/YourFeature`).
5. Open a pull request.

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

RenderEase aims to simplify the template rendering process in PHP projects. With its user-friendly API and flexibility, you can create elegant and efficient templates with ease. Happy coding!