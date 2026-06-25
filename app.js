let ventas = [];
let ingresoTotal = 0;
let equiposInventario = [];
let marcaSeleccionada = '';

function obtenerMarcaPrincipal(texto) {
    if (!texto) return "OTROS";
    let marcaLimpia = texto.trim().split(/\s+/)[0].toUpperCase();
    if (marcaLimpia === 'RETMI') marcaLimpia = 'REDMI';
    return marcaLimpia;
}

async function cargarEquipos() {
    try {
        const response = await fetch(`api_obtener.php`);
        if (!response.ok) throw new Error(`Error: ${response.status}`);
        
        equiposInventario = await response.json();
        equiposInventario.sort((a, b) => a.precio - b.precio);
        renderizarMarcas(); 
    } catch (error) {
        console.error("Error al cargar:", error);
        alert("No se pudieron cargar los equipos.");
    }
}

function renderizarMarcas() {
    const contenedorMarcas = document.getElementById('brand-list');
    if(!contenedorMarcas) return; 
    contenedorMarcas.innerHTML = '';

    const marcasUnicas = [...new Set(equiposInventario.map(e => obtenerMarcaPrincipal(e.marca)))].sort();

    const coloresMarcas = {
        'HONOR': '#0ea5e9', 'INFINIX': '#22c55e', 'IPHONE': '#ef4444',
        'MOTOROLA': '#3b82f6', 'POCO': '#eab308', 'REDMI': '#ea580c',
        'SAMSUNG': '#1d4ed8', 'ZTE': '#06b6d4', 'NEO': '#475569'
    };

    marcasUnicas.forEach(marca => {
        const btn = document.createElement('button');
        btn.className = 'brand-btn';
        btn.innerText = marca;
        
        const colorBase = coloresMarcas[marca] || '#94a3b8'; 
        btn.style.backgroundColor = colorBase;

        if(marca === 'POCO') {
            btn.style.textShadow = 'none';
            btn.style.color = '#1e293b';
        }
        
        btn.onclick = () => {
            marcaSeleccionada = marca;
            document.getElementById('seccion-marcas').style.display = 'none';
            document.getElementById('seccion-modelos').style.display = 'block';
            
            const tituloModulo = document.getElementById('titulo-marca');
            tituloModulo.innerText = marca;
            tituloModulo.style.color = colorBase; 
            
            const modelosFiltrados = equiposInventario.filter(e => obtenerMarcaPrincipal(e.marca) === marca);
            renderizarModelos(modelosFiltrados);
        };
        contenedorMarcas.appendChild(btn);
    });
}

function volverAMarcas() {
    marcaSeleccionada = '';
    document.getElementById('seccion-modelos').style.display = 'none';
    document.getElementById('seccion-marcas').style.display = 'block';
}

function renderizarModelos(listaEquipos) {
    const contenedor = document.getElementById('grid-equipos');
    contenedor.innerHTML = ''; 

    if(listaEquipos.length === 0) {
        contenedor.innerHTML = '<p style="text-align:center; padding: 20px;">No hay equipos para mostrar.</p>';
        return;
    }

    listaEquipos.forEach(equipo => {
        let s_cab1 = parseInt(equipo.stock_cab1) || 0;
        let s_cab2 = parseInt(equipo.stock_cab2) || 0;
        let s_modelo = parseInt(equipo.stock_modelo) || 0;
        let p_credito = parseFloat(equipo.precioCredito) || 0; 
        
        let stockTotal = s_cab1 + s_cab2 + s_modelo;
        const colorTitulo = stockTotal > 0 ? '#27ae60' : '#e74c3c'; 
        
        const card = document.createElement('div');
        card.className = 'card-modelo';
        
        // AQUÍ ESTÁ EL NUEVO BOTÓN DE BORRAR EN LA CABECERA DEL EQUIPO
        card.innerHTML = `
            <div class="model-header" style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 10px;">
                <div>
                    <h2 style="color: ${colorTitulo}; margin: 0; font-size: 1.3rem; font-weight: 800;">
                        ${equipo.nombre}
                    </h2>
                    <span style="font-size: 0.8rem; color: #888;">IMEI: ${equipo.imei} | ${equipo.rom} / ${equipo.ram}GB</span>
                </div>
                <button onclick="eliminarEquipo('${equipo.id}')" style="background: #e74c3c; color: white; border: none; border-radius: 6px; padding: 6px 10px; font-size: 0.8rem; cursor: pointer; font-weight: bold; box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);" title="Eliminar este equipo por completo">
                    🗑️ Borrar
                </button>
            </div>

            <div style="background: #f8f9fa; border-radius: 10px; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd;">
                <p style="margin: 0 0 10px 0; font-size: 0.8rem; font-weight: bold; color: #666; text-transform: uppercase; text-align: center;">Stock por Tienda</p>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: bold; color: #333; font-size: 0.95rem;">🏢 CAB 1</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button onclick="modificarStock('${equipo.id}', 'stock_cab1', -1)" style="color: #e74c3c; width: 28px; height: 28px; border: none; border-radius: 5px; font-weight: bold; cursor:pointer; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">-</button>
                        <span style="font-size: 1rem; font-weight: bold; width: 20px; text-align: center;">${s_cab1}</span>
                        <button onclick="modificarStock('${equipo.id}', 'stock_cab1', 1)" style="color: #27ae60; width: 28px; height: 28px; border: none; border-radius: 5px; font-weight: bold; cursor:pointer; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">+</button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: bold; color: #333; font-size: 0.95rem;">🏢 CAB 2</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button onclick="modificarStock('${equipo.id}', 'stock_cab2', -1)" style="color: #e74c3c; width: 28px; height: 28px; border: none; border-radius: 5px; font-weight: bold; cursor:pointer; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">-</button>
                        <span style="font-size: 1rem; font-weight: bold; width: 20px; text-align: center;">${s_cab2}</span>
                        <button onclick="modificarStock('${equipo.id}', 'stock_cab2', 1)" style="color: #27ae60; width: 28px; height: 28px; border: none; border-radius: 5px; font-weight: bold; cursor:pointer; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">+</button>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: bold; color: #333; font-size: 0.95rem;">🏪 MODELO</span>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button onclick="modificarStock('${equipo.id}', 'stock_modelo', -1)" style="color: #e74c3c; width: 28px; height: 28px; border: none; border-radius: 5px; font-weight: bold; cursor:pointer; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">-</button>
                        <span style="font-size: 1rem; font-weight: bold; width: 20px; text-align: center;">${s_modelo}</span>
                        <button onclick="modificarStock('${equipo.id}', 'stock_modelo', 1)" style="color: #27ae60; width: 28px; height: 28px; border: none; border-radius: 5px; font-weight: bold; cursor:pointer; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">+</button>
                    </div>
                </div>
            </div>

            <div style="background: #fdfdfd; padding: 10px; border-radius: 10px; border: 1px dashed #ccc; margin-bottom: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; align-items: center;">
                    <label style="color: #555; font-size: 0.85rem;">Costo:</label>
                    <div>S/ <input type="number" id="costo-${equipo.id}" value="${equipo.precioCompra}" style="width: 60px; text-align:right; border: 1px solid #ccc; border-radius: 5px; font-size: 0.9rem;"></div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; align-items: center;">
                    <label style="color: #004a99; font-weight: bold; font-size: 0.85rem;">Venta Contado:</label>
                    <div>S/ <input type="number" id="precio-${equipo.id}" value="${equipo.precio}" style="width: 60px; text-align:right; border: 1px solid #bbd6f8; background: #eef5ff; color: #004a99; border-radius: 5px; font-weight: bold; font-size: 0.9rem;"></div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; align-items: center;">
                    <label style="color: #e67e22; font-weight: bold; font-size: 0.85rem;">Venta Crédito:</label>
                    <div>S/ <input type="number" id="preciocredito-${equipo.id}" value="${p_credito}" style="width: 60px; text-align:right; border: 1px solid #f8c471; background: #fdf2e9; color: #d35400; border-radius: 5px; font-weight: bold; font-size: 0.9rem;"></div>
                </div>
                
                <button onclick="guardarPrecios('${equipo.id}')" style="background: #3498db; color: white; border: none; padding: 8px; border-radius: 5px; cursor: pointer; width: 100%; font-size: 0.85rem; margin-top: 5px;">
                    💾 Guardar Precios
                </button>
            </div>

            <button class="btn-registrar" onclick="registrarVenta('${equipo.id}')" style="width: 100%; padding: 12px; border: none; border-radius: 8px; background: #2ecc71; color: white; font-weight: bold; cursor: pointer; font-size: 1rem;">
                Registrar Venta
            </button>
        `;
        contenedor.appendChild(card);
    });
}

// === NUEVA FUNCIÓN PARA ELIMINAR EQUIPO ===
async function eliminarEquipo(idEquipo) {
    const confirmacion = confirm("⚠️ ¿Estás totalmente seguro de que quieres ELIMINAR este equipo de la base de datos? Esta acción no se puede deshacer.");
    if (!confirmacion) return;

    try {
        const response = await fetch('api_eliminar.php', {
            method: 'POST',
            body: JSON.stringify({ id: idEquipo })
        });
        const res = await response.json();
        
        if(res.success) {
            alert("🗑️ Equipo eliminado con éxito.");
            // Recargamos el inventario desde cero para actualizar la pantalla
            cargarEquipos();
            
            // Si el usuario estaba viendo una marca, la cerramos para evitar errores gráficos
            if(marcaSeleccionada) {
                volverAMarcas();
            }
        } else {
            alert("Hubo un problema al eliminar el equipo.");
        }
    } catch(e) { 
        alert("Error de conexión al intentar eliminar."); 
    }
}

async function guardarPrecios(id) {
    const nuevoCosto = parseFloat(document.getElementById(`costo-${id}`).value);
    const nuevoPrecio = parseFloat(document.getElementById(`precio-${id}`).value);
    const nuevoPrecioCredito = parseFloat(document.getElementById(`preciocredito-${id}`).value) || 0;

    if (isNaN(nuevoCosto) || isNaN(nuevoPrecio)) return alert("Ingresa precios válidos.");

    try {
        const response = await fetch('api_actualizar_precios.php', {
            method: 'POST',
            body: JSON.stringify({ id: id, precio_compra: nuevoCosto, precio_venta: nuevoPrecio, precio_credito: nuevoPrecioCredito })
        });
        const res = await response.json();
        if(res.success) {
            const equipo = equiposInventario.find(e => e.id == id);
            equipo.precioCompra = nuevoCosto;
            equipo.precio = nuevoPrecio;
            equipo.precioCredito = nuevoPrecioCredito;
            
            equiposInventario.sort((a, b) => a.precio - b.precio);
            
            if (marcaSeleccionada) {
                renderizarModelos(equiposInventario.filter(e => obtenerMarcaPrincipal(e.marca) === marcaSeleccionada));
            }
            alert("✅ Precios guardados permanentemente.");
        }
    } catch (e) { alert("Error de conexión al guardar."); }
}

async function modificarStock(id, columnaTienda, cambio) {
    const equipo = equiposInventario.find(e => e.id == id);
    let stockActual = parseInt(equipo[columnaTienda]) || 0;
    const nuevoStock = stockActual + cambio;
    if (nuevoStock < 0) return;

    try {
        const response = await fetch('api_stock.php', {
            method: 'POST',
            body: JSON.stringify({ id: id, columna: columnaTienda, stock: nuevoStock })
        });
        const res = await response.json();
        
        if(res.success) {
            equipo[columnaTienda] = nuevoStock; 
            if (marcaSeleccionada) {
                renderizarModelos(equiposInventario.filter(e => obtenerMarcaPrincipal(e.marca) === marcaSeleccionada));
            } else {
                filtrarEquipos(); 
            }
        }
    } catch (e) { alert("Error al actualizar stock"); }
}

async function registrarVenta(idEquipo) {
    const equipo = equiposInventario.find(e => e.id == idEquipo);
    
    const opcion = prompt("¿De qué tienda se realizó la venta?\\nEscribe el número:\\n1 = CAB 1\\n2 = CAB 2\\n3 = MODELO");
    if(!opcion) return; 

    let columnaTiendaDeducir = '';
    if (opcion === '1') columnaTiendaDeducir = 'stock_cab1';
    else if (opcion === '2') columnaTiendaDeducir = 'stock_cab2';
    else if (opcion === '3') columnaTiendaDeducir = 'stock_modelo';
    else return alert("Opción no válida. Venta cancelada.");

    let stockActual = parseInt(equipo[columnaTiendaDeducir]) || 0;

    if(stockActual <= 0) {
        if(!confirm("Esa tienda no tiene stock. ¿Registrar venta de todas formas?")) return;
    }

    const precioVendido = parseFloat(document.getElementById(`precio-${idEquipo}`).value);
    const costoActual = parseFloat(document.getElementById(`costo-${idEquipo}`).value);

    try {
        const response = await fetch('api_vender.php', {
            method: 'POST',
            body: JSON.stringify({ id: idEquipo, columna: columnaTiendaDeducir })
        });
        const resultado = await response.json();

        if(resultado.success) {
            const gananciaReal = precioVendido - costoActual;
            ventas.push({
                Tienda: columnaTiendaDeducir.replace('stock_', '').toUpperCase(), 
                Marca: equipo.marca, Modelo: equipo.nombre, IMEI: equipo.imei,
                Precio_Compra: `S/ ${costoActual}`, Precio_Vendido: `S/ ${precioVendido}`,
                GANANCIA_NETA: `S/ ${gananciaReal}`, Fecha: new Date().toLocaleDateString(), Hora: new Date().toLocaleTimeString()
            });
            ingresoTotal += precioVendido;
            
            if(document.getElementById('contador')) document.getElementById('contador').innerText = `Ventas: ${ventas.length}`;
            if(document.getElementById('totalVentas')) document.getElementById('totalVentas').innerText = `Total Ingresos: S/ ${ingresoTotal}`;
            
            alert(`✅ Venta registrada.\\nGanancia: S/ ${gananciaReal}`);
            
            equipo[columnaTiendaDeducir] -= 1; 
            if (marcaSeleccionada) renderizarModelos(equiposInventario.filter(e => obtenerMarcaPrincipal(e.marca) === marcaSeleccionada));
        }
    } catch (error) { console.error(error); }
}

function filtrarEquipos() {
    const textoBuscado = document.getElementById('buscador').value.toLowerCase();
    if(textoBuscado.length > 0) {
        document.querySelectorAll('.brand-btn').forEach(b => b.classList.remove('active'));
        marcaSeleccionada = '';
    }
    const filtrados = equiposInventario.filter(equipo => 
        equipo.nombre.toLowerCase().includes(textoBuscado) || 
        equipo.marca.toLowerCase().includes(textoBuscado) ||
        equipo.imei.toLowerCase().includes(textoBuscado)
    );
    renderizarModelos(filtrados);
}

function abrirModal() { document.getElementById('modal-agregar').style.display = 'flex'; }
function cerrarModal() { document.getElementById('modal-agregar').style.display = 'none'; }

async function guardarNuevoEquipo() {
    const nuevoEquipo = {
        marca: document.getElementById('n-marca').value.toUpperCase(),
        nombre: document.getElementById('n-nombre').value,
        imei: document.getElementById('n-imei').value || "S/N",
        rom: document.getElementById('n-rom').value,
        ram: document.getElementById('n-ram').value,
        precioCompra: parseFloat(document.getElementById('n-precio-compra').value) || 0,
        precio: parseFloat(document.getElementById('n-precio').value) || 0,
        precioCredito: parseFloat(document.getElementById('n-precio-credito').value) || 0,
        stock_cab1: 0, stock_cab2: 0, stock_modelo: 0
    };
    
    try {
        const response = await fetch('api_agregar.php', { method: 'POST', body: JSON.stringify(nuevoEquipo) });
        const res = await response.json();
        if(res.success) { 
            cerrarModal(); 
            cargarEquipos(); 
            alert("Equipo agregado exitosamente.");
        }
    } catch (e) { alert("Error al agregar equipo."); }
}

function descargarReporte() {
    if (ventas.length === 0) return alert("No hay ventas registradas.");
    const worksheet = XLSX.utils.json_to_sheet(ventas);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, `Ventas`);
    XLSX.writeFile(workbook, `Reporte_Ventas.xlsx`);
}

function descargarInventario() {
    if (equiposInventario.length === 0) return alert("No hay equipos para exportar.");

    const inventarioOrdenado = [...equiposInventario].sort((a, b) => {
        const marcaA = obtenerMarcaPrincipal(a.marca);
        const marcaB = obtenerMarcaPrincipal(b.marca);
        if (marcaA < marcaB) return -1;
        if (marcaA > marcaB) return 1;
        if (a.nombre < b.nombre) return -1;
        if (a.nombre > b.nombre) return 1;
        return 0;
    });

    const encabezados = ["MARCA", "MODELO", "ROM", "RAM", "PRECIO CONTADO", "PRECIO CRÉDITO", "CAB 1", "CAB 2", "MODELO"];
    let dataExcel = [encabezados];

    inventarioOrdenado.forEach(equipo => {
        dataExcel.push([
            obtenerMarcaPrincipal(equipo.marca), 
            equipo.nombre, 
            equipo.rom, 
            equipo.ram, 
            `S/ ${equipo.precio}`, 
            `S/ ${equipo.precioCredito || 0}`, 
            equipo.stock_cab1 || 0, 
            equipo.stock_cab2 || 0, 
            equipo.stock_modelo || 0
        ]);
    });

    const worksheet = XLSX.utils.aoa_to_sheet(dataExcel);

    worksheet['!cols'] = [
        { wch: 15 }, { wch: 25 }, { wch: 10 }, { wch: 10 }, 
        { wch: 18 }, { wch: 18 }, { wch: 10 }, { wch: 10 }, { wch: 10 } 
    ];

    const rango = XLSX.utils.decode_range(worksheet['!ref']);
    for (let R = rango.s.r; R <= rango.e.r; ++R) {
        for (let C = rango.s.c; C <= rango.e.c; ++C) {
            const celda = worksheet[XLSX.utils.encode_cell({ r: R, c: C })];
            if (!celda) continue;

            if (R === 0) {
                celda.s = {
                    font: { bold: true, color: { rgb: "FFFFFF" } },
                    fill: { fgColor: { rgb: "004A99" } },
                    alignment: { horizontal: "center", vertical: "center" }
                };
            } else {
                celda.s = { alignment: { horizontal: "center", vertical: "center" } };
                if(C === 4) celda.s.font = { color: { rgb: "004A99" }, bold: true }; 
                if(C === 5) celda.s.font = { color: { rgb: "D35400" }, bold: true }; 
            }
        }
    }

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Inventario_General");
    XLSX.writeFile(workbook, `Reporte_Inventario_Imprimir.xlsx`);
}

cargarEquipos();