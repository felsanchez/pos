const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();
  
  try {
    console.log("1. Authenticating via login_dev.php...");
    await page.goto('http://localhost/pos/login_dev.php');
    await page.waitForLoadState('networkidle');
    console.log("Redirected to:", page.url());

    console.log("2. Opening Agregar Gasto modal...");
    await page.click('button[data-target="#modalAgregarGasto"]');
    await page.waitForSelector('#modalAgregarGasto', { state: 'visible' });

    console.log("3. Filling in new gasto form...");
    await page.fill('input[name="nuevoConceptoGasto"]', 'Playwright Gasto Test');
    await page.fill('input[name="nuevoMontoGasto"]', '150.75');
    await page.selectOption('select[name="nuevaCategoriaGasto"]', { index: 1 });
    await page.fill('input[name="nuevoNumeroComprobante"]', 'COMP-PW-789');
    await page.fill('textarea[name="nuevasNotasGasto"]', 'Automated test notes');

    console.log("4. Submitting form...");
    // Since swal is used, we might need to handle the sweetalert
    await Promise.all([
      page.click('#modalAgregarGasto button[type="submit"]'),
      page.waitForSelector('.swal2-confirm, .confirm') // Wait for swal confirm button
    ]);

    console.log("5. Confirming sweetalert...");
    await page.click('.swal2-confirm, .confirm');
    await page.waitForLoadState('networkidle');

    console.log("6. Finding the newly created gasto in table...");
    // Reload/wait just in case
    await page.waitForTimeout(2000);
    
    // Click the edit button for the first row (the newly created one)
    console.log("7. Clicking edit button of the first row...");
    await page.click('.btnEditarGasto:first-of-type');
    await page.waitForSelector('#modalEditarGasto', { state: 'visible' });
    await page.waitForTimeout(1000); // Wait for AJAX to populate

    console.log("8. Verifying N° Comprobante value in edit modal...");
    const val = await page.inputValue('#editarNumeroComprobante');
    console.log("N° Comprobante value is:", val);

    if (val === 'COMP-PW-789') {
      console.log("SUCCESS: N° Comprobante is correctly saved and displayed!");
    } else {
      console.log("FAILURE: N° Comprobante is not saved or not displayed! Value found:", val);
    }

    console.log("9. Editing N° Comprobante...");
    await page.fill('#editarNumeroComprobante', 'COMP-PW-MODIFIED');
    
    console.log("10. Saving changes...");
    await Promise.all([
      page.click('#modalEditarGasto button[type="submit"]'),
      page.waitForSelector('.swal2-confirm, .confirm')
    ]);
    await page.click('.swal2-confirm, .confirm');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    console.log("11. Re-opening edit modal to verify modification...");
    await page.click('.btnEditarGasto:first-of-type');
    await page.waitForSelector('#modalEditarGasto', { state: 'visible' });
    await page.waitForTimeout(1000);

    const valModified = await page.inputValue('#editarNumeroComprobante');
    console.log("Modified N° Comprobante value is:", valModified);

    if (valModified === 'COMP-PW-MODIFIED') {
      console.log("SUCCESS: Modified N° Comprobante is correctly saved and displayed!");
    } else {
      console.log("FAILURE: Modified N° Comprobante is not saved or not displayed! Value found:", valModified);
    }

  } catch (err) {
    console.error("Test failed with error:", err);
  } finally {
    await browser.close();
  }
})();
