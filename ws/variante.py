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
"https://www.nike.com/es/t/air-max-90-zapatillas-rcvY2qOH/CN8490-002",
"https://www.nike.com/es/t/air-max-dn-zapatillas-B2FF75FT/IB7025-001",
"https://www.nike.com/es/t/shox-tl-zapatillas-2HdNdke8/AV3595-005",
"https://www.nike.com/es/t/revolution-7-zapatillas-de-running-asfalto-Gtwmps/FB2207-007",
"https://www.nike.com/es/t/kiger-10-zapatillas-de-trail-running-WRtG09/IB7668-001",
"https://www.nike.com/es/t/reactx-rejuven8-zapatillas-YiVrJ2n9/HV5060-002",
"https://www.nike.com/es/t/killshot-2-leather-zapatillas-VXZ3KW/432997-107",
"https://www.nike.com/es/t/wildhorse-10-trail-zapatillas-de-trail-running-6RHZHLQc/FV2338-004",



   # Puedes añadir más URLs aquí
]

for url in urls:
    print(f"\n--- Navegando a: {url} ---")
    try:
        driver.get(url)

        # Esperar que carguen los elementos principales que representan las variantes de producto
        # !!! ADVERTENCIA: Selector CSS ".css-1wg28dk" es inestable y propenso a cambios
        WebDriverWait(driver, 10).until(
                EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".css-kxjrd9"))
            )
      
        time.sleep(3) # Aumentada ligeramente por precaución en sitios pesados como Nike

        cards = driver.find_elements(By.CSS_SELECTOR, ".css-kxjrd9")



        for idx, card in enumerate(cards, start=1):

          
                image_urls = card.find_elements(By.CSS_SELECTOR, ".css-1wud0ww")

           
                for img_idx, img_element in enumerate(image_urls, start=1):
                        img_url = img_element.get_attribute('href')
                        # Opcional: Puedes añadir un check para url_img being None or empty string
                        if img_url:
                             print(f"{img_url}")


                print("  " + "-" * 20) # Separador entre variantes/tarjetas


    except Exception as e:
        

        print("\n--- Fin del proceso de scraping ---")

# Cerrar el navegador
driver.quit()
print("Navegador cerrado.")