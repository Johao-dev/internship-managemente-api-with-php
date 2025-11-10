# API de gestión de practicantes

Este repo es solo una traducción del lenguaje de la API definida en [este repositorio](https://www.google.com)
en el cual se usaba TypeScript junto al framework NestJS.

En este repositorio replico el mismo funcionamiento (con algunas variaciones en los endpoints) usando el
lenguaje PHP.

---

## Flujo de datos

El flujo de datos para una petición en esta api es el siguiente:

### 1. api.php

Toda petición se hace al archivo api.php. Este archivo se encarga de enrutar las peticiones a los
enrutadores individuales de cada módulo; este proceso lo hace leyendo el parámetro de consulta
"resource" que viene en la petición.

### 2. JwtFilter.php

Antes de que api.php redirija la petición al enrutador independiente se ejecuta un "filtro"
que evalúa si el encabezado "Authorization" esta presente, si lo esta toma el token JWT del encabezado
y autentica al usuario, devolviendo el código de estado apropiado si no se logra autenticar
correctamente al usuario. Sino esta presente el encabezado, asume que la petición va dirigida a
una ruta pública.

La idea del filtro la tomé de como se maneja la autenticación en Spring Security. Su equivalente
en Laravel sería el Middleware.

### 3. XRouter.php

Cada módulo dentro de `src/` cuenta con un archivo XRouter.php (`UserRouter.php`, `InternRouter.php`, etc.)
La tarea principal de estos archivos es redirijir la petición al método del XController.php (`UserController.php`,
`InternController.php`, etc.) adecuado.

### 4. XController.php y XValidator.php

Cada XController.php para cualquier petición que haga una operación de escritura (POST, PUT, PATCH) antes de enviar
los datos al XService.php correspondiente ejecuta un método de su respectivo XValidator.php (`UserValidator.php`, etc.)
el cual se encargará de validar los datos de entrada.


**El flujo siguiente es el mismo para cada módulo**:

Después de validar los datos de entrada se ejecuta el método del XService apropiado, éste XService puede usar otro
XService.php de otro módulo o su propio XRepository.php para peticiones que requieren recuperar datos de una base de
datos. Estos XRepository.php trabajan con una clase StoredProcedureExecutor qué, como su nombre lo indica, se encarga
de ejecutar un procedimiento almacenado de la base de datos y devolver los resultados en una clase de PHP.

También dentro de cada módulo hay una carpeta `dtos/` que almacena todo los DTOs de entrada y salida que devuelve la
API.
