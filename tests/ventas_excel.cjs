const fs = require('fs'), vm = require('vm'), assert = require('assert/strict');
const XLSX = require('xlsx-js-style');
const context = {XLSX};
vm.createContext(context);
vm.runInContext(fs.readFileSync(require('path').join(__dirname,'../js/reporte_ventas.js'),'utf8'),context);
const base = {id:1,creado_en:'2026-11-08 16:05:10',tienda:'stock_cab1',marca:'SAMSUNG',modelo:'A16',
    rom:'128GB',ram:'4GB',imei:'001234567890123',dni:'00123456',telefono:'987654321',
    tipo:'contado',financiera:'',precio_centimos:50050,inicial_centimos:0,descuento_centimos:2020,total_centimos:48030};
const rows = [base,{...base,id:2,tienda:'stock_cab2',tipo:'credito',financiera:'PayJoy',
    precio_centimos:90000,inicial_centimos:10010,descuento_centimos:1005,total_centimos:9005}];
const workbook = context.crearLibroVentas(rows);
const buffer = XLSX.write(workbook,{type:'buffer',bookType:'xlsx'});
const restored = XLSX.read(buffer,{type:'buffer',cellNF:true});
const sheet = restored.Sheets.Ventas;
assert.equal(sheet['!autofilter'].ref,'A1:T3');
assert.equal(sheet.I2.v,'001234567890123');
assert.equal(sheet.J2.v,'00123456');
assert.equal(sheet.I2.t,'s');
assert.equal(sheet.D2.v,'QHATU');
assert.equal(sheet.D3.v,'CAB');
assert.equal(sheet.P2.v,480.30);
assert.equal(sheet.T3.v,90.05);
assert.equal(sheet.B2.t,'n');
assert.equal(restored.Sheets.Resumen.B5.v,570.35);
assert(!sheet.Q2 || sheet.Q2.v === undefined);
assert(!sheet.N3 || sheet.N3.v === undefined);
const empty=context.crearLibroVentas([]);
assert.equal(empty.Sheets.Resumen.B2.v,0);
console.log('PASS: XLSX round-trip, filters, text identifiers, dates, stores, cash/credit and summary.');
