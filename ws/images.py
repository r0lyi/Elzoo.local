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
"https://www.nike.com/es/t/strike-pantalon-de-futbol-repelente-al-agua-M2d1Pv/HJ3804-013",
"https://www.nike.com/es/t/strike-pantalon-de-futbol-dri-fit-FP1xXR/FN2405-010",
"https://www.nike.com/es/t/pro-mallas-de-fitness-dri-fit-de-3-4-4l51Jb/FB7950-010",
"https://www.nike.com/es/t/pro-mallas-de-fitness-dri-fit-fXHRcv/FB7952-010",
"https://www.nike.com/es/t/pro-pantalon-corto-de-fitness-dri-fit-vPKn5N/FB7958-010",
"https://www.nike.com/es/t/pro-pantalon-corto-de-fitness-dri-fit-mwBNFS/FB7963-010",
"https://www.nike.com/es/t/unlimited-pantalon-versatil-con-bajos-con-cremallera-dri-fit-adv-FG0DTyCc/FB7548-010",
"https://www.nike.com/es/t/phenom-pantalon-de-running-dri-fit-de-tejido-knit-JckXMJ/DQ4740-010",
"https://www.nike.com/es/t/therma-pantalon-de-fitness-entallado-WctKY7dK/DQ5405-010",
"https://www.nike.com/es/t/acg-black-iguana-pantalon-2-en-1-wYTL0xo7/HJ2891-010",


   # Puedes añadir más URLs aquí
]

for url in urls:
    try:
        driver.get(url)

        # Esperar que carguen los elementos principales que representan las variantes de producto
        # !!! ADVERTENCIA: Selector CSS ".css-1wg28dk" es inestable y propenso a cambios
        WebDriverWait(driver, 10).until(
                EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".css-1wg28dk"))
            )
      


        # !!! ADVERTENCIA: time.sleep es menos eficiente y fiable que WebDriverWait específico
        # Una pequeña espera extra si los elementos con imágenes tardan en cargarse DESPUÉS de que el contenedor aparece
        time.sleep(3) # Aumentada ligeramente por precaución en sitios pesados como Nike

        # Extraer todos los elementos que representan las "tarjetas" o variantes del producto
        # !!! ADVERTENCIA: Selector CSS ".css-1wg28dk" es inestable
        cards = driver.find_elements(By.CSS_SELECTOR, ".css-1wg28dk")

     

        for idx, card in enumerate(cards, start=1):

          
                image_elements = card.find_elements(By.CSS_SELECTOR, "img")

           
                for img_idx, img_element in enumerate(image_elements, start=1):
                        img_url = img_element.get_attribute('src')
                        # Opcional: Puedes añadir un check para url_img being None or empty string
                        if img_url:
                             print(f"{img_url}")


                print("  " + "-" * 20) # Separador entre variantes/tarjetas


    except Exception as e:
        print(f"ERROR general al procesar {url}: {e}")
        # Opcional: Puedes intentar hacer una captura de pantalla para debug en caso de error general
        # driver.save_screenshot(f"error_general_{url.replace('https://','').replace('/','_').replace(':','')}.png")
        continue # Continuar con la siguiente URL si hay un error en la actual

print("\n--- Fin del proceso de scraping ---")

# Cerrar el navegador
driver.quit()
print("Navegador cerrado.")