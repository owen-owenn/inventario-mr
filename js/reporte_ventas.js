'use strict';
function crearLibroVentas(ventas) {
    const nombres = {stock_cab1:'QHATU', stock_cab2:'CAB', stock_modelo:'Modelo'};
    const encabezados = ['N° venta','Fecha (Perú)','Hora (Perú)','Tienda','Marca','Modelo','ROM','RAM',
        'IMEI vendido','DNI cliente','Teléfono cliente','Tipo de venta','Financiera',
        'Precio contado (S/)','Descuento contado (S/)','Total contado (S/)',
        'Precio crédito (S/)','Inicial (S/)','Descuento inicial (S/)','Inicial final (S/)'];
    const filas = ventas.map(v => {
        const contado = v.tipo === 'contado';
        const [fecha, hora] = v.creado_en.split(' ');
        // Fecha numérica de Excel, sin convertir por la zona horaria del navegador.
        const fechaExcel = (Date.parse(fecha + 'T00:00:00Z') - Date.UTC(1899,11,30)) / 86400000;
        return [Number(v.id), fechaExcel, hora, nombres[v.tienda] || v.tienda, v.marca, v.modelo,
            v.rom, v.ram, String(v.imei), String(v.dni), String(v.telefono),
            contado ? 'Contado' : 'Crédito', v.financiera || '',
            contado ? Number(v.precio_centimos)/100 : null,
            contado ? Number(v.descuento_centimos)/100 : null,
            contado ? Number(v.total_centimos)/100 : null,
            contado ? null : Number(v.precio_centimos)/100,
            contado ? null : Number(v.inicial_centimos)/100,
            contado ? null : Number(v.descuento_centimos)/100,
            contado ? null : Number(v.total_centimos)/100];
    });
    const ws = XLSX.utils.aoa_to_sheet([encabezados, ...filas]);
    ws['!autofilter'] = {ref: 'A1:T' + (filas.length + 1)};
    ws['!cols'] = [12,15,14,16,18,28,14,14,23,17,20,18,22,...Array(7).fill(25)].map(wch=>({wch}));
    const headerStyle = {font:{bold:true,color:{rgb:'FFFFFF'}},fill:{fgColor:{rgb:'004A99'}},
        alignment:{vertical:'center',wrapText:true}};
    ws['!rows'] = [{hpt:32}];
    encabezados.forEach((_,c)=>{ws[XLSX.utils.encode_cell({r:0,c})].s=headerStyle;});
    filas.forEach((_,r)=>{
        for(let c=0;c<20;c++){
            const celda=ws[XLSX.utils.encode_cell({r:r+1,c})];
            if(!celda) continue;
            celda.s={fill:{fgColor:{rgb:r%2 ? 'FFFFFF':'EFF5FB'}},alignment:{vertical:'center'}};
            if(c===1) celda.z='dd/mm/yyyy';
            if(c>=8 && c<=10) celda.z='@';
            if(c>=13) celda.z='"S/ "#,##0.00';
        }
    });
    const contar = ventas.length;
    const sumar = columna => filas.reduce((total,f)=>total+(f[columna]||0),0);
    const resumen = XLSX.utils.aoa_to_sheet([
        ['RESUMEN DE VENTAS','Importe / cantidad'],
        ['Ventas registradas',contar],
        ['Cobrado al contado (S/)',sumar(15)],
        ['Iniciales cobradas (S/)',sumar(19)],
        ['Total cobrado: contado + iniciales (S/)',sumar(15)+sumar(19)],
        ['Valor de ventas a crédito (S/)',sumar(16)],
        ['Descuentos al contado (S/)',sumar(14)],
        ['Descuentos de inicial (S/)',sumar(18)],
        ['Zona horaria','Perú (America/Lima)'],
        ['Uso','Filtra la hoja Ventas por fecha, tienda, marca, tipo o financiera.'],
        ['Nota','El precio crédito no equivale al dinero cobrado al registrar la venta.']
    ]);
    resumen['!cols']=[{wch:49},{wch:77}];
    resumen.A1.s=headerStyle; resumen.B1.s=headerStyle;
    for(let i=3;i<=8;i++) resumen['B'+i].z='"S/ "#,##0.00';
    const libro = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(libro, ws, 'Ventas');
    XLSX.utils.book_append_sheet(libro, resumen, 'Resumen');
    return libro;
}
async function descargarReporte() {
    const boton = document.getElementById('reporte-ventas');
    if(boton?.disabled) return;
    if(boton) {boton.disabled=true;boton.textContent='Generando reporte…';}
    try {
        if(typeof XLSX==='undefined') throw new Error('No se cargó Excel. Revisa tu conexión y recarga la página.');
        const respuesta = await fetch('api_ventas.php',{cache:'no-store'});
        const datos = await respuesta.json();
        if(!respuesta.ok || !Array.isArray(datos.ventas)) throw new Error(datos.error || 'No se pudo consultar el reporte.');
        if(!datos.ventas.length) {alert('Todavía no hay ventas registradas.');return;}
        XLSX.writeFile(crearLibroVentas(datos.ventas), 'Reporte_Ventas_' + new Date().toISOString().slice(0,10) + '.xlsx');
    } catch(error) {
        alert(error instanceof SyntaxError ? 'El servidor no devolvió un reporte válido. Intenta nuevamente.' : error.message);
    } finally {
        if(boton) {boton.disabled=false;boton.textContent='📊 Reporte de ventas Excel';}
    }
}
