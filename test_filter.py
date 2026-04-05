import asyncio
from playwright.async_api import async_playwright

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        errors = []
        page.on('console', lambda msg: errors.append(f"[CONSOLE] {msg.text}") if msg.type == 'error' else None)
        page.on('pageerror', lambda exception: errors.append(f"[PAGE ERROR] {exception}"))
        
        try:
            print("Logging in...")
            await page.goto("http://localhost/pos/ingreso")
            await page.fill('input[name="ingUsuario"]', "admin")
            await page.fill('input[name="ingPassword"]', "admin")
            await page.click('button[type="submit"]')
            await page.wait_for_timeout(2000)
            
            print("Navigating to gastos...")
            await page.goto("http://localhost/pos/gastos")
            await page.wait_for_timeout(2000)
            
            print("Testing filtering click...")
            await page.click('#btnFiltrarGastos')
            await page.wait_for_timeout(2000)
            
            print("\nCheck if AJAX was successful by inspecting console or DOM.")
            print(f"Errors Found ({len(errors)}):")
            for e in errors:
                print(e)
                
            # Check if table body has content
            count = await page.evaluate("() => document.querySelectorAll('#tablaGastos tbody tr').length")
            print(f"Table rows after filter click: {count}")
            
        except Exception as e:
            print(f"Playwright error: {e}")
        finally:
            await browser.close()

asyncio.run(run())
