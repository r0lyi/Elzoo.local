import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import pymysql


# ——— Configuración de Selenium ———
chrome_options = Options()
chrome_options.binary_location = "/usr/bin/chromium-browser"
chrome_options.add_argument("--no-sandbox")
chrome_options.add_argument("--disable-dev-shm-usage")
service = Service("/usr/bin/chromedriver")
driver = webdriver.Chrome(service=service, options=chrome_options)

# …y luego, en lugar de mysql.connector.connect, usa:
db = pymysql.connect(
    host="localhost",
    user="usuario",
    password="CASA%rsg2005",
    database="Elzoo",
    charset="utf8mb4",
    cursorclass=pymysql.cursors.Cursor
)
cursor = db.cursor()

# ——— URLs a scrapear ———
urls = [
    "https://www.zoomadrid.com/animales-y-continentes/animales/panda-gigante",
    "https://www.zoomadrid.com/animales-y-continentes/animales/elefante-asiatico",
    "https://www.zoomadrid.com/animales-y-continentes/animales/jirafa",
    "https://www.zoomadrid.com/animales-y-continentes/animales/oso-del-tibet",
    "https://www.zoomadrid.com/animales-y-continentes/animales/panda-rojo",
    "https://www.zoomadrid.com/animales-y-continentes/animales/gorila-de-costa",
    "https://www.zoomadrid.com/animales-y-continentes/animales/loris-arco-iris",
    "https://www.zoomadrid.com/animales-y-continentes/animales/muntjac-de-reeves",
    "https://www.zoomadrid.com/animales-y-continentes/animales/gamo",
    "https://www.zoomadrid.com/animales-y-continentes/animales/tortuga-de-seychelles-o-de-aldabra",
    "https://www.zoomadrid.com/animales-y-continentes/animales/buho-real",
    "https://www.zoomadrid.com/animales-y-continentes/animales/nu-de-cola-blanca",
    "https://www.zoomadrid.com/animales-y-continentes/animales/ganso-cenizo",
    "https://www.zoomadrid.com/animales-y-continentes/animales/ciervo-europeo",
    "https://www.zoomadrid.com/animales-y-continentes/animales/pingueino-de-jackass",
    "https://www.zoomadrid.com/animales-y-continentes/animales/camello-bactriano",
    "https://www.zoomadrid.com/animales-y-continentes/animales/lemur-variegatus",
    "https://www.zoomadrid.com/animales-y-continentes/animales/wallaby-de-roca",
    "https://www.zoomadrid.com/animales-y-continentes/animales/nandu",
    "https://www.zoomadrid.com/animales-y-continentes/animales/emu",
    "https://www.zoomadrid.com/animales-y-continentes/animales/arrui",
    "https://www.zoomadrid.com/animales-y-continentes/animales/guanaco",
    "https://www.zoomadrid.com/animales-y-continentes/animales/bisonte-europeo",
    "https://www.zoomadrid.com/animales-y-continentes/animales/mono-capuchino",
    "https://www.zoomadrid.com/animales-y-continentes/animales/alimoche",
    "https://www.zoomadrid.com/animales-y-continentes/animales/tortuga-de-espolones",
    "https://www.zoomadrid.com/animales-y-continentes/animales/suricata",
    "https://www.zoomadrid.com/animales-y-continentes/animales/calao-terrestre",
    "https://www.zoomadrid.com/animales-y-continentes/animales/vison-europeo",
]

# ——— Sentencia INSERT respetando el orden de columnas ———
sql_insert = """
INSERT INTO animales (
    nombre,
    nombre_cientifico,
    clase,
    continente,
    habitat,
    dieta,
    peso,
    tamano,
    informacion,
    sabias,
    imagen,
    fecha_nacimiento,
    sexo
) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
"""

for url in urls:
    driver.get(url)
    try:
        # Esperar título
        nombre = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".attraction-title"))
        ).text.strip()

        # Características en lista
        etiquetas = WebDriverWait(driver, 10).until(
            EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".text-label"))
        )
        datos_car = [e.text.strip() for e in etiquetas if e.text.strip()]

        # Descripción larga
        parrafos = driver.find_elements(By.CSS_SELECTOR, ".ca04_textrich p")
        informacion = "\n".join(p.text.strip() for p in parrafos if p.text.strip())

        # "¿Sabías qué...?"
        try:
            sabias = driver.find_element(By.CSS_SELECTOR, ".information-text__subtitle").text.strip()
        except:
            sabias = None

        # URL de la imagen principal
        try:
            img_el = driver.find_element(By.CSS_SELECTOR, ".padding-gallery img")
            imagen = img_el.get_attribute("src")
        except:
            imagen = None

        # Mapear características por posición (con fallback a "Desconocido")
        nombre_cientifico = datos_car[0] if len(datos_car) > 0 else None
        clase           = datos_car[1] if len(datos_car) > 1 else None
        continente      = datos_car[2] if len(datos_car) > 2 else None
        habitat         = datos_car[3] if len(datos_car) > 3 else None
        dieta           = datos_car[4] if len(datos_car) > 4 else None
        peso            = datos_car[5] if len(datos_car) > 5 else None
        tamano          = datos_car[6] if len(datos_car) > 6 else None

        # Como no hay fecha de nacimiento ni sexo en la página, los guardamos como NULL/'desconocido'
        fecha_nac = None
        sexo = 'desconocido'

        # Preparamos la tupla de datos en el orden correcto
        valores = (
            nombre,
            nombre_cientifico,
            clase,
            continente,
            habitat,
            dieta,
            peso,
            tamano,
            informacion or None,
            sabias,
            imagen,
            fecha_nac,
            sexo
        )

        cursor.execute(sql_insert, valores)
        print(f"Insertado: {nombre}")

    except Exception as e:
        print(f"Error en {url}: {e}")
        continue

# Confirmar inserciones y cerrar todo
db.commit()
cursor.close()
db.close()
driver.quit()
