import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import NoSuchElementException

# Configurar opciones de Chromium (sin headless para que se vea el navegador)
chrome_options = Options()
chrome_options.binary_location = "/usr/bin/chromium-browser"
chrome_options.add_argument("--no-sandbox")
chrome_options.add_argument("--disable-dev-shm-usage")

# Configurar ChromeDriver
service = Service("/usr/bin/chromedriver")

# Inicializar WebDriver (se abrirá la ventana del navegador)
driver = webdriver.Chrome(service=service, options=chrome_options)

# Única URL a scrapear
url = "https://www.adidas.es/calcetines-hombre"

driver.get(url)

try:
    # Esperar que cargue al menos una tarjeta de producto
    WebDriverWait(driver, 10).until(
        EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".product-card_product-card__a9BIh"))
    )
    time.sleep(2)  # espera extra para la carga de precios/descripciones

    # Extraer todas las tarjetas
    cards = driver.find_elements(By.CSS_SELECTOR, ".product-card_product-card__a9BIh")

    print(f"\nExtrayendo datos de productos de: {url}\n")
    for idx, card in enumerate(cards, start=1):
        # Nombre (siempre existe)
        nombre = card.find_element(By.CSS_SELECTOR, ".product-card-description_name__xHvJ2").text.strip()

    # Descripción / subtítulo (puede faltar)
        try:
            description = card.find_element(By.CSS_SELECTOR, ".product-card-description_info__z_CcT").text.strip()
        except NoSuchElementException:
            description = "(sin descripción)"
    # Precio (puede faltar)
        try:
            precio = card.find_element(By.CSS_SELECTOR, "._priceComponent_1dbqy_14").text.strip()
        except NoSuchElementException:
            precio = "(sin precio)"
        try:
            imagen = card.find_element(By.CSS_SELECTOR, ".product-card-assets_product-image__OvGsb").get_attribute('src')
        except NoSuchElementException:
            imagen = "(sin imagen)"
            

        # Impresión formateada
        print(f"{idx}. nombre: {nombre}")
        print(f"   description: {description}")
        print(f"   precio: {precio}")
        print(f"   imagen: {imagen}\n")



    print("="*60 + "\n")

except Exception as e:
    print(f"Error al extraer datos de {url}: {e}")

# Mantener el navegador abierto unos segundos para ver resultados (opcional)
time.sleep(5)

# Cerrar el navegador
driver.quit()
