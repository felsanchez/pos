const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  try {
    await page.goto('http://localhost/pos/ingreso');
    if (await page.$('input[name="ingUsuario"]') !== null) {
      await page.fill('input[name="ingUsuario"]', 'admin');
      await page.fill('input[name="ingPassword"]', 'admin');
      await Promise.all([
          page.waitForNavigation(),
          page.click('button[type="submit"]')
      ]);
    }
    await page.goto('http://localhost/pos/gastos');
    await page.waitForTimeout(3000);
    const hasWrapper = await page.evaluate(() => document.querySelector('#tablaGastos_wrapper') !== null);
    const hasSearch = await page.evaluate(() => document.querySelector('.dataTables_filter') !== null);
    console.log("Wrapper exists:", hasWrapper);
    console.log("Search exists:", hasSearch);
    if (!hasSearch) {
      console.log(await page.evaluate(() => document.querySelector('.box').outerHTML));
    }
  } catch (err) {
    console.error(err);
  } finally {
    await browser.close();
  }
})();
