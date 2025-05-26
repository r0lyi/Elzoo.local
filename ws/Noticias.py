import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
import mysql.connector  # type: ignore
import time
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC


# Conexión a la base de datos
con = mysql.connector.connect(
    host="localhost",
    user="usuario",
    password="CASA%rsg2005",
    database="Elzoo"
)
cursor = con.cursor()

# Configuración de Chromium
chrome_options = Options()
chrome_options.binary_location = "/usr/bin/chromium-browser"
service = Service("/usr/bin/chromedriver")

# Inicializar WebDriver
driver = webdriver.Chrome(service=service, options=chrome_options)

# URL objetivo
url = "https://elpais.com/noticias/animales/"
driver.get(url)

# Esperar que cargue el contenido
time.sleep(7)

# Buscar noticias
noticias = driver.find_elements(By.CLASS_NAME, "c")

for noticia in noticias:
    try:
        titulo = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME,"c"))
        )

        titulo = noticia.find_element(By.TAG_NAME, "h2").text
        descripcion = noticia.find_element(By.TAG_NAME, "p").text
        imagen = noticia.find_element(By.TAG_NAME, "img").get_attribute("src")
        url_origen = noticia.find_element(By.CSS_SELECTOR, "a").get_attribute("href")

        print(f"Titular: {titulo}\nDescripción: {descripcion}\nImagen: {imagen}\nURL: {url_origen}\n")

        # Insertar en la base de datos
        cursor.execute(
            "INSERT INTO noticias (titulo, descripcion, imagen, url_origen) VALUES (%s, %s, %s, %s)",
            (titulo, descripcion, imagen, url_origen)
        )
        con.commit()

    except Exception as e:
        print("Error al procesar una noticia:", e)

# Cerrar conexiones
cursor.close()
con.close()
driver.quit()
