import asyncio
from playwright.async_api import async_playwright

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page()
        
        errors = []
        page.on('console', lambda msg: errors.append(f"[CONSOLE] {msg.type}: {msg.text}") if msg.type == 'error' else None)
        page.on('pageerror', lambda exception: errors.append(f"[PAGE ERROR] {exception}"))
        
        try:
            print("Navigating to login...")
            await page.goto("http://localhost/pos/ingreso")
            await page.wait_for_timeout(1000)
            
            user_input = await page.query_selector('input[name="ingUsuario"]')
            if user_input:
                print("Logging in...")
                await user_input.fill("admin")
                await page.fill('input[name="ingPassword"]', "admin")
                
                async with page.expect_navigation():
                    await page.click('button[type="submit"]')
            else:
                print("Already logged in or login failed")
                
            print("Navigating to gastos...")
            await page.goto("http://localhost/pos/gastos")
            await page.wait_for_timeout(3000)
            
            wrapper = await page.query_selector('.dataTables_wrapper')
            search = await page.query_selector('.dataTables_filter')
            
            print(f"Wrapper exists: {wrapper is not None}")
            print(f"Search exists: {search is not None}")
            
            print("\nErrors Found:")
            for e in errors:
                print(e)
                
            if not wrapper:
                html = await page.content()
                print("HTML slice:", html[html.find('tablaGastos'):html.find('tablaGastos')+500])
                
        except Exception as e:
            print(f"Playwright error: {e}")
        finally:
            await browser.close()

asyncio.run(run())
