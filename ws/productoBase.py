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
url = "https://www.zoomadrid.com/descubre-el-zoo/animales-y-continentes/animales"

driver.get(url)

try:
    # Esperar que cargue al menos una tarjeta de producto
    WebDriverWait(driver, 10).until(
        EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".row"))
    )
    time.sleep(2)  # espera extra para la carga de precios/descripciones

    # Extraer todas las tarjetas
    cards = driver.find_elements(By.CSS_SELECTOR, ".row")

    print(f"\nExtrayendo datos de productos de: {url}\n")
    for idx, card in enumerate(cards, start=1):
        # Nombre (siempre existe)
        nombre = card.find_element(By.CSS_SELECTOR, ".title").text.strip()

    

        # Impresión formateada
        print(f"{idx}. nombre: {nombre}")



    print("="*60 + "\n")

except Exception as e:
    print(f"Error al extraer datos de {url}: {e}")

# Mantener el navegador abierto unos segundos para ver resultados (opcional)
time.sleep(5)

# Cerrar el navegador
driver.quit()
