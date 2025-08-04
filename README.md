
# Instala dependencias
composer install


# Migramos la base de datos en mysql
php artisan migrate

# Generamos una base de datos con el nombre de 
DB_DATABASE=test_botsapp



# variables que tiene que tener en el .env

TWILIO_SID=ACf574ee94445ef8f04686d117da5f789e
TWILIO_AUTH_TOKEN=5eb86c320de4d45ba948f830f55ca94a
TWILIO_WHATSAPP_FROM="whatsapp: +14155238886"

OPENAI_API_KEY=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




# Para correlo local 
php artisan serve

# Se usa ngrok para ponerlo en twilio en sandbox seting(Si es la version gratis de ngrok la url cambia y esta limitado 4 horas la misma url)
ngrok http 8000


# Configurar Twilio Sandbox 

1. Regístrate en Twilio.

2. Ponemos en .ENV los ID que da al registrase en la variable TWILIO_SID del archivo .ENV y el token en la variable TWILIO_AUTH_TOKEN Igual en el archivo del .ENV 

3. Ir a la seccion de Messaging -> Try it out -> Send a WhatsApp message

4. Escanea el QR al telefono donde se hara la prueba y envia el codigo de verificacion

5. Ponemos el numero dado por twilo en el archivo .ENV en la variable TWILIO_WHATSAPP_FROM el numero de telefono 

6. Configuramos la URL de Webhook en el Sandbox a lado de dond escaneamos el QR vamos donde dice Sandbox settings en la area que dice When a message comes in ponemos  Url_generada_por_ngrok/api/webhook/whatsapp y aseguramos que alado en Method diga POST

# Ya que se ejecute el ngrok vamos a twilio al area que dice "Send a WhatsApp message" en el navbar que dice Sanbox settings y denajo del area que dice "When a message comes in" ponemos la url generada por ngrok y se le agrega el "/webhook/whatsapp" todo pegado porque es donde recibe el mensaje al momento de que el usuario lo mande y permite contestar con OpenAI:

Url_generada/webhook/whatsapp


# Para hacer las pruebas de envio se recomienda antes mandar el codigo para recibir y enviar en las pruebas

# los endpoint para ejecutarlo

| Método | Endpoint                           | Descripción                                      |
| ------ | ---------------------------------- | ------------------------------------------------ |
| POST   | /api/webhook/whatsapp              | Recibe mensajes entrantes en twilio y contesta   |
| POST   | /api/send                          | Envia mensajes                                   |
| GET    | /api/conversations/{phone}         | Muestra historial de conversación de un teléfono |
| GET    | /api/conversations                 | Lista teléfonos únicos                           |
| GET    | /api/conversations/{phone}/summary | Genera resumen de conversacion                   |

# Se recomienda usar postman para probarlo con BODY: From y Body.


