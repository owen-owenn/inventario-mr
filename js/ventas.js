'use strict';
const form = document.getElementById('venta-form');
const campos = document.getElementById('campos');
const mensaje = document.getElementById('mensaje');
const marca = document.getElementById('marca');
const equipo = document.getElementById('equipo');
const tienda = document.getElementById('tienda');
const tipo = document.getElementById('tipo');
const tiendas = {stock_cab1: 'CAB 1', stock_cab2: 'CAB 2', stock_modelo: 'Modelo'};
let equipos = [];
let enviando = false;
const campo = nombre => form.elements.namedItem(nombre);
const centimos = valor => Math.round(Number(valor || 0) * 100);
const soles = valor => 'S/ ' + (valor / 100).toFixed(2);
function aviso(texto, clase = '') {
    mensaje.textContent = texto;
    mensaje.className = clase;
}
function opciones(select, texto) {
    select.replaceChildren(new Option(texto, ''));
}
function marcaPrincipal(texto) {
    const valor = String(texto || '').trim().split(/\s+/)[0].toUpperCase();
    return valor === 'RETMI' ? 'REDMI' : valor || 'OTROS';
}
function cambiarMarca(preseleccion = '') {
    opciones(equipo, 'Selecciona el equipo');
    equipos.filter(e => marcaPrincipal(e.marca) === marca.value).forEach(e => {
        const etiqueta = [e.nombre, e.rom, e.ram, 'IMEI: ' + (e.imei || 'S/N'), '#' + e.id].filter(Boolean).join(' · ');
        equipo.add(new Option(etiqueta, e.id));
    });
    equipo.value = preseleccion;
    cambiarEquipo();
}
function cambiarEquipo() {
    opciones(tienda, 'Selecciona la tienda');
    const seleccionado = equipos.find(e => String(e.id) === equipo.value);
    if (!seleccionado) return;
    Object.entries(tiendas).forEach(([columna, nombre]) => {
        const stock = Number(seleccionado[columna]) || 0;
        const opcion = new Option(nombre + ' · ' + stock + ' disponibles', columna);
        opcion.disabled = stock <= 0;
        tienda.add(opcion);
    });
}
function cambiarTipo() {
    ['contado', 'credito'].forEach(nombre => {
        const activo = tipo.value === nombre;
        const bloque = document.getElementById(nombre);
        bloque.hidden = !activo;
        bloque.querySelectorAll('input').forEach(input => {
            input.disabled = !activo;
            input.required = activo && !input.name.startsWith('descuento');
            input.setCustomValidity('');
        });
    });
    calcular();
}
function calcular() {
    const contado = tipo.value === 'contado';
    const precio = campo(contado ? 'precio_contado' : 'precio_credito');
    const base = contado ? precio : campo('inicial');
    const descuento = campo(contado ? 'descuento_contado' : 'descuento_inicial');
    const excede = centimos(descuento.value) > centimos(base.value);
    descuento.setCustomValidity(excede ? 'El descuento no puede superar el importe.' : '');
    campo('inicial').setCustomValidity(!contado && centimos(base.value) > centimos(precio.value) ? 'La inicial no puede superar el precio crédito.' : '');
    const total = document.getElementById('total');
    total.textContent = !tipo.value ? 'Selecciona contado o crédito.' : excede
        ? 'Revisa el descuento: supera el importe.'
        : (contado ? 'Precio final: ' : 'Inicial final a cobrar: ') + soles(centimos(base.value) - centimos(descuento.value));
}
async function cargar() {
    campos.disabled = true;
    document.getElementById('recargar').hidden = true;
    try {
        const response = await fetch('api_obtener.php', {cache:'no-store'});
        const data = await response.json();
        if (!response.ok || !Array.isArray(data)) throw new Error();
        equipos = data;
        opciones(marca, 'Selecciona la marca');
        [...new Set(equipos.map(e => marcaPrincipal(e.marca)))].sort().forEach(m => marca.add(new Option(m, m)));
        const seleccionado = equipos.find(e => String(e.id) === new URLSearchParams(location.search).get('equipo'));
        if (seleccionado) marca.value = marcaPrincipal(seleccionado.marca);
        cambiarMarca(seleccionado ? String(seleccionado.id) : '');
        campos.disabled = equipos.length === 0;
        aviso(equipos.length ? 'Completa los tres pasos para registrar la venta.' : 'Todavía no hay equipos en el inventario.');
    } catch (e) {
        aviso('No se pudo cargar el inventario. Reintenta la carga.', 'error');
        document.getElementById('recargar').hidden = false;
    }
}
form.addEventListener('submit', async event => {
    event.preventDefault();
    if (enviando) return;
    calcular();
    if (!form.reportValidity()) return;
    const datos = Object.fromEntries(new FormData(form).entries());
    enviando = true;
    campos.disabled = true;
    aviso('Guardando la venta…');
    let guardado = false;
    try {
        const response = await fetch('api_vender.php', {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(datos)
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.error || 'No se pudo guardar la venta.');
        guardado = true;
        aviso('Venta #' + result.id + ' registrada. Se descontó 1 unidad de ' + tiendas[datos.columna] + '. ' +
            (datos.tipo === 'contado' ? 'Precio final: ' : 'Inicial final: ') + soles(result.total_centimos), 'exito');
        document.getElementById('nueva').hidden = false;
    } catch (e) {
        aviso(e instanceof TypeError || e instanceof SyntaxError
            ? 'No se pudo confirmar la respuesta. Reintenta sin cambiar los datos: la misma venta no se descontará dos veces.'
            : e.message, 'error');
    } finally {
        enviando = false;
        campos.disabled = guardado;
    }
});
marca.addEventListener('change', () => cambiarMarca());
equipo.addEventListener('change', cambiarEquipo);
tipo.addEventListener('change', cambiarTipo);
form.addEventListener('input', calcular);
document.getElementById('recargar').addEventListener('click', cargar);
cambiarTipo();
cargar();
