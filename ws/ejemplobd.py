import time
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By  # Importar By para localización de elementos
import mysql.connector # type: ignore



con = mysql.connector.connect(
                                host="localhost", 
                                user="usuario", 
                                password="usuario", 
                                database="Elzoo"
                                )
cursor = con.cursor()






# Configurar opciones de Chromium
chrome_options = Options()
chrome_options.binary_location = "/usr/bin/chromium-browser"  # Ruta de Chromium

# Configurar ChromeDriver
service = Service("/usr/bin/chromedriver")  # Ruta de ChromeDriver

# Inicializar WebDriver
driver = webdriver.Chrome(service=service, options=chrome_options)

# URL de MediaMarkt
url = "https://www.nationalgeographic.com.es/animales"
driver.get(url)

time.sleep(7)


# Encontrar todos los productos en la página
Noticias = driver.find_elements(By.CLASS_NAME, "thumb")
# Iterar sobre los productos para obtener el nombre y el precio
for product in Noticias:
    try:
        # Extraer el nombre del producto
        especie = product.find_element(By.TAG_NAME, "p").text
        imagen = product.find_element(By.TAG_NAME, "img").get_attribute('src')

        # Extraer el precio del producto
      
        print(f"titular: {especie}, Imagen: {imagen}")




        cursor.execute("INSERT INTO animales (especie , imagen ) VALUES (%s, %s)", (especie, imagen))
        con.commit()



    except Exception as e:
        print("Error al extraer el producto:", e)
# Cerrar la conexión a la base de datos




con.close()
cursor.close()


# Cerrar el navegador
driver.quit()