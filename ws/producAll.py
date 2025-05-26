import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import NoSuchElementException, TimeoutException # Importar TimeoutException

# Configurar opciones de Chromium
chrome_options = Options()
# !!! ADVERTENCIA: RUTA FIJA - Considera usar webdriver_manager para portabilidad si no estás en este entorno exacto

chrome_options.binary_location = "/usr/bin/chromium-browser"



service = Service("/usr/bin/chromedriver")



driver = webdriver.Chrome(service=service, options=chrome_options)

# Lista de URLs
urls = [
"https://www.nike.com/es/t/air-max-95-zapatillas-bLt3Z4YZ/CV1635-001",
"https://www.nike.com/es/t/pegasus-trail-5-zapatillas-de-trail-running-rqtK6B/IB7667-001",




   # Puedes añadir más URLs aquí
]

for url in urls:
    try:
        driver.get(url)

 # Nombre (siempre existe)
        nombre = card.find_element(By.CSS_SELECTOR, ".css-1h3ryhm").text.strip()

        # Descripción / subtítulo (puede faltar)
        try:
            description = card.find_element(By.CSS_SELECTOR, ".css-17kvvgb").text.strip()
        except NoSuchElementException:
            description = "(sin descripción)"

        # Precio (puede faltar)
        try:
            precio = card.find_element(By.CSS_SELECTOR, ".css-tbgmka").text.strip()
        except NoSuchElementException:
            precio = "(sin precio)"

        try:
            imagen = card.find_element(By.CSS_SELECTOR, ".css-ba2b72 img").get_attribute('src')
        except NoSuchElementException:
            imagen = "(sin imagen)"

        # Impresión formateada
        print(f"{idx}. nombre: {nombre}")
        print(f"   description: {description}")
        print(f"   precio: {precio}")
        print(f"   imagen: {imagen}\n")


        WebDriverWait(driver, 10).until(
               EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".css-1wg28dk"))
           )
        time.sleep(3)


        cards = driver.find_elements(By.CSS_SELECTOR, ".css-1wg28dk")


    


        for idx, card in enumerate(cards, start=1):
        
               image_elements = card.find_elements(By.CSS_SELECTOR, "img")


         
               for img_idx, img_element in enumerate(image_elements, start=1):
                       img_url = img_element.get_attribute('src')
                       # Opcional: Puedes añadir un check para url_img being None or empty string
                       if img_url:
                            print(f"{img_url}")




               print("  " + "-" * 20)

        

    except Exception as e:

        print("\n--- Fin del proceso de scraping ---")

# Cerrar el navegador
driver.quit()
print("Navegador cerrado.")