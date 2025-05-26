import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# Configurar opciones de Chromium
chrome_options = Options()
chrome_options.binary_location = "/usr/bin/chromium-browser"

# Configurar ChromeDriver
service = Service("/usr/bin/chromedriver")

# Inicializar WebDriver
driver = webdriver.Chrome(service=service, options=chrome_options)

# Lista de URLs   Zapatillas Nike 
urls = [
    "https://www.nike.com/es/t/air-max-dn8-zapatillas-BpF4YX3V/FQ7860-009",# zapatillas 
    "https://www.nike.com/es/t/air-force-1-07-zapatillas-pNCCVs/CW2288-111",# zapatillas 
    "https://www.nike.com/es/t/air-max-plus-zapatillas-x9kqkJ/DM0032-013",# zapatillas 
    "https://www.nike.com/es/t/defy-all-day-zapatillas-de-entrenamiento-s1cPct/DJ1196-001"# zapatillas 
    "https://www.nike.com/es/t/mercurial-vapor-16-elite-air-max-95-se-fg-botas-de-futbol-de-perfil-bajo-terreno-firme-ksi9P9rH/HV9915-001",# zapatillas futbol
    "https://www.nike.com/es/t/mercurial-vapor-16-elite-botas-de-futbol-de-perfil-bajo-fg-bxniipgU/FQ1457-301",# zapatillas futbol
    "https://www.nike.com/es/t/phantom-luna-2-elite-fg-botas-de-futbol-de-perfil-alto-Lr8XMz/FJ2572-800",# zapatillas futbol
    "https://www.nike.com/es/t/mercurial-vapor-16-elite-fg-as-botas-de-futbol-de-perfil-bajo-terreno-firme-p3Yvzylw/FQ8683-500",# zapatillas futbol
    "https://www.nike.com/es/t/lebron-xxii-zapatillas-de-baloncesto-PzdHqMLW/HV8451-400",# zapatillas baloncesto
    "https://www.nike.com/es/t/lebron-nxxt-genisus-zapatillas-de-baloncesto-RGjsDZio/HF0712-602",# zapatillas baloncesto
    "https://www.nike.com/es/t/ja-2-zapatillas-de-baloncesto-87xLNX/FD7328-100",# zapatillas baloncesto
    "https://www.nike.com/es/t/pegasus-41-zapatillas-de-running-asfalto-bffEF4sw/FD2722-002",# zapatillas running
    "https://www.nike.com/es/t/pegasus-trail-5-zapatillas-de-trail-running-zsdkaeGC/DV3864-102",# zapatillas running
    "https://www.nike.com/es/t/revolution-7-zapatillas-de-running-asfalto-Gtwmps/FB2207-400",# zapatillas running
    "https://www.nike.com/es/t/nikecourt-lite-4-zapatillas-de-tenis-RmcfD5/FD6575-100",# zapatillas tennis
    "https://www.nike.com/es/t/crosscourt-zapatillas-nino-a-y-nino-a-pequeno-a-vvKnN4/FN2231-100",# zapatillas tennis
    "https://www.nike.com/es/t/gp-challenge-pro-zapatillas-de-tenis-de-pista-rapida-WZdzDV/FB3146-100",# zapatillas tennis
]

for url in urls:
    driver.get(url)
    try:
        # Esperar hasta que el nombre esté presente
        nombre_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".css-1h3ryhm"))
        )
        nombre = nombre_element.text

  # Esperar hasta que el nombre esté presente
        descripcion_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".css-17kvvgb"))
        )
        descripcion = descripcion_element.text

         # Esperar hasta que el nombre esté presente
        nombre_precio = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".css-tbgmka"))
        )
        precio = nombre_precio.text
        
        nombre_imagen = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.TAG_NAME, "img"))
        )
        imagen = nombre_imagen.get_attribute('src')
       

        # Mostrar los datos estructurados
        print(f"name: {nombre}")
        print(f"short_description: {descripcion}")

        print(f"price: {precio}")

        print(f"imge_url: {imagen}")



      

        print("\n" + "="*50 + "\n")

    except Exception as e:
        print(f"Error al extraer datos de {url}: {e}")
        continue

# Cerrar el navegador
driver.quit()