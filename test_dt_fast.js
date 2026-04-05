const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();
  
  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.log([JS ERROR] );
    }
  });
  page.on('pageerror', exception => {
    console.log([PAGE ERROR] );
  });

  try {
    await page.goto('http://localhost/pos/login');
    if (await page.input[name="ingUsuario"] !== null) {
      await page.fill('input[name="ingUsuario"]', 'admin');
      await page.fill('input[name="ingPassword"]', 'admin');
      await page.click('button[type="submit"]');
      await page.waitForTimeout(2000);
    }
    
    await page.goto('http://localhost/pos/gastos');
    await page.waitForTimeout(3000);
    
    const wrapper = await page..dataTables_wrapper;
    if (wrapper) {
      console.log("DATATABLES WRAPPER FOUND LITERALLY");
    } else {
      console.log("DATA TABLES WRAPPER MISSING");
      const html = await page.evaluate(() => document.body.innerHTML);
      console.log("PAGE HTML EXCERPT:");
      console.log(html.substring(html.indexOf('box-body'), html.indexOf('box-body') + 2000));
    }
  } catch (e) {
    console.error("Test framework error: ", e.message);
  } finally {
    await browser.close();
  }
})();
