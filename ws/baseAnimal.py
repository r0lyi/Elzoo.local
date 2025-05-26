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

# Lista de URLs
urls = [
    "https://www.zoomadrid.com/animales-y-continentes/animales/panda-gigante",
    "https://www.zoomadrid.com/animales-y-continentes/animales/elefante-asiatico",
    "https://www.zoomadrid.com/animales-y-continentes/animales/jirafa",
    "https://www.zoomadrid.com/animales-y-continentes/animales/oso-del-tibet",
    "https://www.zoomadrid.com/animales-y-continentes/animales/panda-rojo",
]

for url in urls:
    driver.get(url)
    try:
        # Esperar hasta que el nombre esté presente
        nombre_element = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".attraction-title"))
        )
        nombre = nombre_element.text

        # Obtener todos los textos de las características
        etiquetas = WebDriverWait(driver, 10).until(
            EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".text-label"))
        )
        
        # Extraer textos y limpiar datos
        caracteristicas = [etiqueta.text.strip() for etiqueta in etiquetas if etiqueta.text.strip()]

        # Nueva extracción: párrafos de .ca04_textrich
        contenedores_info = WebDriverWait(driver, 10).until(
            EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".ca04_textrich p"))
        )

        # Almacenar toda la información en una variable y limpiar datos
        informacion = "\n".join([parrafo.text.strip() for parrafo in contenedores_info if parrafo.text.strip()])
        # Asignar a variables específicas con manejo de índices

        Sabias_que = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".information-text__subtitle"))
        )
        sabias = Sabias_que.text

        imagen_animal = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".padding-gallery"))
        )

        imagen = imagen_animal.find_element(By.TAG_NAME, "img").get_attribute('src')

        try:
            ncientifico = caracteristicas[0]
            clase = caracteristicas[1]
            continente = caracteristicas[2]
            habitat = caracteristicas[3]
            dieta = caracteristicas[4]
            peso = caracteristicas[5]
            tamaño = caracteristicas[6] if len(caracteristicas) > 6 else "Desconocido"
        except IndexError:
            # Manejar casos donde falten datos
            ncientifico = caracteristicas[0] if len(caracteristicas) > 0 else "Desconocido"
            clase = caracteristicas[1] if len(caracteristicas) > 1 else "Desconocido"
            continente = caracteristicas[2] if len(caracteristicas) > 2 else "Desconocido"
            habitat = caracteristicas[3] if len(caracteristicas) > 3 else "Desconocido"
            dieta = caracteristicas[4] if len(caracteristicas) > 4 else "Desconocido"
            peso = caracteristicas[5] if len(caracteristicas) > 5 else "Desconocido"
            tamaño = caracteristicas[6] if len(caracteristicas) > 6 else "Desconocido"

        # Mostrar los datos estructurados
        print(f"imagen: {imagen}")
        print(f"Animal: {nombre}")
        print(f"Nombre científico: {ncientifico}")
        print(f"Clase: {clase}")
        print(f"Continente: {continente}")
        print(f"Hábitat: {habitat}")
        print(f"Dieta: {dieta}")
        print(f"Peso: {peso}")
        print(f"Tamaño: {tamaño}")
        print(f"Descripción: {informacion }")
        print(f"Sabias: {sabias}")


        print("\n" + "="*50 + "\n")

    except Exception as e:
        print(f"Error al extraer datos de {url}: {e}")
        continue

# Cerrar el navegador
driver.quit()