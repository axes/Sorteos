# 🎉 Sistema de Sorteos 🎉

Bienvenido al **Sistema de Sorteos** - una aplicación web dinámica y robusta para gestionar sorteos en línea. Este sistema permite cargar participantes desde archivos CSV, realizar sorteos de forma aleatoria y asignar ganadores con diferentes opciones de configuración.




### ⚠️ Disclaimer ⚠️
*Esta aplicación es un prototipo en desarrollo y puede presentar errores, bugs o limitaciones en sus funcionalidades. No corresponde a una versión final ni ha sido completamente testeada. Algunas características pueden no estar terminadas o estar sujetas a cambios en futuras iteraciones. Por favor, úsala con fines de prueba o aprendizaje y no para entornos de producción.*


## 📋 Características

- **Cargar Participantes**: Permite la carga masiva de participantes a través de archivos CSV (nombres, email, rut)
- **Opciones de Sorteo**: Selección de diversos tipos de sorteos, desde un solo ganador hasta múltiples posiciones.
- **Funcionalidad de Baneo**: Posibilidad de excluir participantes en futuros sorteos.
- **Historial de Concursos**: Consulta histórica de concursos en los que ha participado un usuario a través de API.


## ⚙️ Instalación

1. Clona el repositorio en tu máquina:
   ```bash
   git clone https://github.com/tu_usuario/nombre_repositorio.git
   cd nombre_repositorio 
   ```

2. Instala las dependencias necesarias y configura los archivos de autenticación (ver config/auth.php y config/database.php).

3. Configura la base de datos:
    
    - Crea las tablas ejecutando los archivos de migración en database/migrations/.
Ejecuta la aplicación en tu servidor local. Si usas Apache y WSL:
    ``` bash
    sudo service apache2 start
    ```

## 🚀 Uso

1. **Inicia sesión:** Ingresa con las credenciales establecidas en auth.php.
2. **Crea un Sorteo:** Desde el panel principal, selecciona Crear Sorteo o Editar Sorteo.
3. **Carga Participantes:** Sube un archivo CSV para añadir los participantes al sorteo.
4. **Realiza el Sorteo:** Escoge el tipo de sorteo y selecciona los ganadores de manera aleatoria.
5. **Consulta Pública de Resultados:** Puedes hacer públicos los resultados y permitir el acceso a la vista pública de los ganadores.

## 🛠 API Gateway

**Endpoint de Reportes:**
    Consulta concursos de un participante por RUT y API Key.

- URL: /api/concursos/{rut}
- Método: GET
- Parámetros:
    - rut: RUT del participante (sin puntos ni guiones).
    - api_key: Clave de API válida.

Ejemplo de consulta:

``` bash
curl -X GET 'http://localhost/sorteos/api/concursos/123456789?api_key=TU_API_KEY'
```

## 🔒 Seguridad
- Asegúrate de mantener la API Key y archivos de configuración en .gitignore para proteger datos sensibles.
- Configura auth.php y database.php de forma segura antes de subir cambios al repositorio.


## 👥 Contribuciones
¡Las contribuciones son bienvenidas! Si deseas mejorar el sistema o agregar nuevas funcionalidades, siéntete libre de enviar un PR.

--- 
Desarrollado con ❤️+⌛+✨+💻+☕ por Gino M. Villablanca A. (Axes).

