# Registro de ventas de equipos

En el inicio, **Subir venta** está junto al acceso por contraseña. También aparece dentro del inventario y en el botón **Registrar Venta** de cada equipo.

1. Cliente: DNI peruano de 8 dígitos y celular peruano de 9 dígitos.
2. Contado: precio y descuento opcional. Final = precio - descuento.
   Crédito: financiera, precio crédito, inicial y descuento opcional de la inicial. Inicial final = inicial - descuento. No se resta este descuento al precio crédito.
3. Marca, equipo existente y tienda (CAB 1, CAB 2 o Modelo). Cada envío vende una unidad.

El formulario de ventas es accesible desde el inicio sin la contraseña del inventario. No expone un listado de clientes ni ventas. CSRF protege los envíos entre sitios, pero no reemplaza una cuenta de vendedor; quien tenga acceso a esta página puede registrar ventas.

## Base de datos

Usa la conexión existente de db.php. La primera venta crea automáticamente ventas_equipos con ventas_schema.sql, si todavía no existe. El usuario MySQL necesita permiso CREATE para esta instalación; alternativamente, ejecutar ese SQL una vez con una cuenta administradora.

Las tablas equipos y ventas_equipos deben usar InnoDB. Si no lo hacen, el registro se rechaza sin modificar stock. No se convierte ni borra ninguna tabla automáticamente.

Cada venta conserva DNI, teléfono, tienda, tipo, financiera, datos del equipo al momento de vender, importes en céntimos y fecha del servidor. No se guarda información de clientes en GitHub. La tabla existente reportes y su módulo de caja no se modifican.

La venta y el descuento de stock se confirman en una transacción, con bloqueo del equipo. Una clave única por formulario impide duplicar un envío al reintentar. Para una nueva venta, usar **Registrar otra venta**.

## Comprobaciones

La acción **Verificar ventas** ejecuta revisión de sintaxis e integración PHP/MySQL con una base desechable llamada ventas_test. Comprueba contado, crédito, descuentos, validaciones, CSRF, falta de stock, reversión ante errores y ventas simultáneas. El test copia únicamente los archivos necesarios a una carpeta temporal y crea su propia conexión; nunca carga db.php de producción.

Para ejecutarlo localmente se necesitan PHP con mysqli, Python 3 y MySQL local con la base y credenciales de prueba definidas en tests/ventas_integration.py:

    VENTAS_TEST_DB=ventas_test python3 tests/ventas_integration.py

Si el alojamiento no despliega automáticamente desde main, desplegar el commit completo con todos sus archivos.
