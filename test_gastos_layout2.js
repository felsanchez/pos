const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  
  try {
    await page.goto('http://localhost/pos/ingreso');
    if (await page.$('#ingUsuario')) {
      await page.fill('#ingUsuario', 'admin');
      await page.fill('#ingPassword', 'admin');
      await page.click('button[type="submit"]');
      await page.waitForNavigation();
    }
    await page.goto('http://localhost/pos/gastos');
    await page.waitForTimeout(3000);
    
    const html = await page.evaluate(() => document.querySelector('.box-header').outerHTML + document.querySelector('.box-body').outerHTML);
    console.log(html);
  } catch (err) {
    console.error(err);
  } finally {
    await browser.close();
  }
})();
