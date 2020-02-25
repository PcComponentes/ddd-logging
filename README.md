# DDD Logging
Pequeño middleware para [messenger](https://symfony.com/doc/current/components/messenger.html), el bus de symfony, para loggear todo lo que ocurra durante la ejecución del resto de middlewares.
Se sugiere que este middleware sea de los primeros en meterse en el bus.

## Funcionamiento
La idea del middleware es muy simple. Para construirlo, requiere de cuatro dependencias:
- Instancia de logger PSR, por ejemplo, monolog.
- instancia del tracker. Véase el siguiente punto.
- Serializador de mensajes, para guardar una foto del mensaje procesado.
- Serializador de excepciones, para logear si llega a ocurrir, la foto de la excepción disparada.

## Tracker
Esta clase sirve para meter en el registro de logs el ID de una posible operación "padre", para posteriormente poder sacar trazas de tipo "árbol" por un sistema que sea capaz de centralizar estos logs.