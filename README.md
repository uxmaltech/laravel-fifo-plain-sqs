# Instalación
```
composer require uxmaltech/laravel-fifo-plain-sqs
```

# Publicar archivo de configuración
```
php artisan vendor:publish
```
- Seleccionar la opción: **Uxmal\FifoPlainSqs\SqsFifoPlainServiceProvider**

# Configurar handler de SQS
En el archivo `config/sqs-plain.php` cambiar los diferentes handlers de tu aplicación:
```php
<?php

/**
 * List of plain SQS queues and their corresponding handling classes
 */

return [
    'handlers' => [
        'default.fifo' => App\Jobs\DefaultHandlerJob::class,
    ],

    'default-handler' => App\Jobs\DefaultHandlerJob::class,
];
```

# Configurar .env
En el archivo `.env` agregar las siguientes variables de entorno:
```
QUEUE_CONNECTION=fifo-plain-sqs
```