const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();
  
  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.log([Browser Console Error] );
    } else {
      console.log([Browser Console] );
    }
  });
  
  page.on('pageerror', exception => {
    console.log([Browser Exception] );
  });

  try {
    await page.goto('http://localhost/pos/ingreso', { waitUntil: 'networkidle' });
    
    // Login
    if (await page.$('input[name="ingUsuario"]') !== null) {
      console.log("Logging in...");
      await page.fill('input[name="ingUsuario"]', 'admin');
      await page.fill('input[name="ingPassword"]', 'admin');
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
      ]);
      console.log("Logged in successfully");
    } else {
      console.log("Already logged in");
    }
    
    console.log("Navigating to gastos...");
    await page.goto('http://localhost/pos/gastos', { waitUntil: 'networkidle' });
    
    const dtWrapper = await page.evaluate(() => document.querySelector('#tablaGastos_wrapper') !== null);
    console.log("Is DataTable initialized? " + dtWrapper);
    
    if (!dtWrapper) {
      console.log("Dumping table HTML:");
      console.log(await page.evaluate(() => document.querySelector('#tablaGastos').outerHTML.substring(0, 500)));
    }
    
  } catch (err) {
    console.error("Script failed:", err);
  } finally {
    await browser.close();
  }
})();
