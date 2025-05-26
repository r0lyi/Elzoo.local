import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import NoSuchElementException

# Configurar opciones de Chromium
chrome_options = Options()
chrome_options.binary_location = "/usr/bin/chromium-browser"
chrome_options.add_argument("--headless")             # opcional: ejecutar en modo headless
chrome_options.add_argument("--no-sandbox")
chrome_options.add_argument("--disable-dev-shm-usage")

# Configurar ChromeDriver
service = Service("/usr/bin/chromedriver")

# Inicializar WebDriver
driver = webdriver.Chrome(service=service, options=chrome_options)

# Lista de URLs a scrapear
urls = [
    "https://www.nike.com/es/w/hombre-pantalones-cortos-38fphznik1"
    # puedes añadir más URLs aquí
]

for url in urls:
    driver.get(url)
    try:
        # Esperar hasta que al menos un producto esté presente
        WebDriverWait(driver, 10).until(
            EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".product-card"))
        )
        # Pequeña espera extra para carga completa
        time.sleep(2)

        # Encontrar todas las tarjetas de producto
        cards = driver.find_elements(By.CSS_SELECTOR, ".product-card")

        print(f"\nExtrayendo datos de productos de: {url}\n")
        for idx, card in enumerate(cards, start=1):
            # Título
            title = card.find_element(By.CSS_SELECTOR, ".product-card__title").text.strip()
            # Descripción / subtítulo (si existe)
            try:
                subtitle = card.find_element(By.CSS_SELECTOR, ".product-card__subtitle").text.strip()
            except NoSuchElementException:
                subtitle = "(sin descripción)"
            print(f"{idx}. {title}")
            print(f"   ➤ {subtitle}\n")

        print("\n" + "="*50 + "\n")

    except Exception as e:
        print(f"Error al extraer datos de {url}: {e}")
        continue

# Cerrar el navegador
driver.quit()
