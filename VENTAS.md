# Registro de ventas de equipos

En el inicio, **Subir venta** está junto al acceso por contraseña. También aparece dentro del inventario y en el botón **Registrar Venta** de cada equipo.

1. Cliente: DNI peruano de 8 dígitos y celular peruano de 9 dígitos.
2. Contado: precio y descuento opcional. Final = precio - descuento.
   Crédito: financiera, precio crédito, inicial y descuento opcional de la inicial. Inicial final = inicial - descuento. No se resta este descuento al precio crédito.
3. Marca, equipo existente y tienda (QHATU, CAB o Modelo). Cada envío vende una unidad.

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

## Actualización para Railway

- El modelo se selecciona del catálogo; el IMEI de la unidad vendida se escribe aparte (15 dígitos). Los registros nuevos guardan ese IMEI, sin modificar la ficha del modelo.
- QHATU corresponde a stock_cab1; CAB corresponde a stock_cab2. Los identificadores de las columnas y sus cantidades no cambian.
- Dentro del inventario, Reporte de ventas Excel descarga ventas persistidas desde un endpoint que exige la sesión de administrador. Incluye hoja Ventas con autofiltros y hoja Resumen. Las fechas se muestran en hora de Perú; DNI, celular e IMEI se conservan como texto.
- El diagnóstico del servidor confirmó que equipos era MyISAM. La utilidad administrativa diagnostico_ventas.php exige contraseña y token CSRF para convertir tablas MyISAM a InnoDB. Primero crea un respaldo completo dentro de MySQL, bloquea las escrituras durante la copia y conversión, y verifica la cantidad de filas. No elimina el respaldo. Las ventas normales nunca realizan esta conversión.
- Las pruebas incluyen una tabla MyISAM original y se ejecutan en MySQL 8 y 9, además de verificar el Excel generado y el formulario móvil.
