const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  
  const errors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      errors.push(`[Console Error] ${msg.text()}`);
    }
  });
  page.on('pageerror', exception => {
    errors.push(`[Page Error] ${exception}`);
  });

  try {
    await page.goto('http://localhost/pos/ingreso');
    
    // Check if login is needed
    if (await page.$('#ingUsuario')) {
      await page.fill('#ingUsuario', 'admin');
      await page.fill('#ingPassword', 'admin');
      await page.click('button[type="submit"]');
      await page.waitForNavigation();
    }
    
    await page.goto('http://localhost/pos/gastos');
    await page.waitForTimeout(3000); // Wait for datatable to try to initialize
    
    await page.screenshot({ path: 'C:/Users/walek/.gemini/antigravity/brain/a6b96178-30f0-47fb-b85e-3fd00bd31163/artifacts/gastos_layout_debug.png' });
    
    console.log("ERRORS FOUND:");
    console.log(errors.join("\n"));
  } catch (err) {
    console.error("Test script failed:", err);
  } finally {
    await browser.close();
  }
})();
