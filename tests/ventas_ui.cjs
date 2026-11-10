const {chromium} = require(process.env.CODEX_PRIMARY_RUNTIME_NODE_MODULES ? process.env.CODEX_PRIMARY_RUNTIME_NODE_MODULES + '/playwright' : 'playwright');
const fs = require('fs');
const assert = require('assert/strict');
(async()=>{
 const browser = await chromium.launch({headless:true});
 const page = await browser.newPage({viewport:{width:390,height:844}});
 const errors=[]; page.on('pageerror',e=>errors.push(e.message));
 const root = require('path').resolve(__dirname, '..') + '/';
 let html=fs.readFileSync(root+'subir_venta.php','utf8').replace(/<\?php[\s\S]*?\?>/,'').replace(/<\?=[\s\S]*?\?>/g,'a'.repeat(64));
 let requests=[];
 await page.route('http://test.local/**',async route=>{
  const path=new URL(route.request().url()).pathname.slice(1);
  if(path==='api_obtener.php') return route.fulfill({json:[
    {id:1,marca:'SAMSUNG',nombre:'A16',rom:'128GB',ram:'4GB',stock_cab1:3,stock_cab2:0,stock_modelo:1},
    {id:2,marca:'HONOR',nombre:'X7',rom:'256GB',ram:'8GB',stock_cab1:0,stock_cab2:2,stock_modelo:0}
  ]});
  if(path==='api_vender.php'){
   requests.push(route.request().postDataJSON());
   await new Promise(r=>setTimeout(r,150));
   return route.fulfill({status:201,json:{success:true,id:20,total_centimos:48030}});
  }
  if(path==='subir_venta.php') return route.fulfill({contentType:'text/html',body:html});
  return route.fulfill({contentType:path.endsWith('.css')?'text/css':'text/javascript',body:fs.readFileSync(root+path,'utf8')});
 });
 await page.goto('http://test.local/subir_venta.php?equipo=1');
 await page.waitForFunction(()=>!document.getElementById('campos').disabled);
 assert.equal(await page.locator('#marca').inputValue(),'SAMSUNG');
 assert.equal(await page.locator('#equipo').inputValue(),'1');
 assert(await page.locator('#tienda option[value=stock_cab2]').isDisabled());
 assert.deepEqual(await page.locator('#vendedor option').allTextContents(),
    ['Selecciona al vendedor','Jordy','Yeisom','Zinedin','Adriano','Diana','Katherine','Nicol']);
 await page.locator('#vendedor').selectOption('Zinedin');
 await page.locator('[name=dni]').fill('00123456');
 await page.locator('[name=telefono]').fill('987654321');
 await page.locator('[name=imei]').fill('123456789012345');
 await page.locator('#tipo').selectOption('credito');
 await page.locator('[name=financiera]').fill('PayJoy');
 await page.locator('[name=precio_credito]').fill('900');
 await page.locator('[name=inicial]').fill('100.10');
 await page.locator('[name=descuento_inicial]').fill('10.05');
 assert.equal(await page.locator('#total').textContent(),'Inicial final a cobrar: S/ 90.05');
 await page.locator('[name=descuento_inicial]').fill('101');
 assert.equal(await page.locator('[name=descuento_inicial]').evaluate(e=>e.checkValidity()),false);
 await page.locator('#tipo').selectOption('contado');
 assert(await page.locator('[name=financiera]').isDisabled());
 await page.locator('[name=precio_contado]').fill('500.50');
 await page.locator('[name=descuento_contado]').fill('20.20');
 await page.locator('#tienda').selectOption('stock_cab1');
 assert.equal(await page.locator('#total').textContent(),'Precio final: S/ 480.30');
 assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
 
 await page.locator('#guardar').click();
 await page.waitForSelector('#nueva:visible');
 assert.equal(requests.length,1);
 assert.equal(requests[0].vendedor,'Zinedin');
 assert.equal(requests[0].imei,'123456789012345');
 assert.equal(await page.locator('#tienda option[value=stock_cab1]').textContent(),'QHATU · 3 disponibles');
 assert(!('financiera' in requests[0]));
 assert(await page.locator('#guardar').isDisabled());
 assert.deepEqual(errors,[]);
 console.log('PASS: responsive layout, brand/model, stock options, cash/credit totals, invalid discount, submit payload and success');
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
