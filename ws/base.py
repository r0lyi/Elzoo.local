from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
import csv

# URL de la página
cnn_url = "https://www.cactuseros.com/GenerosEspeciesIdentificadas/A.html"

# Función para realizar scraping usando Selenium
def scrape_with_selenium(url):
    options = Options()
    options.headless = False  # Cambiar a True si deseas ejecutar en modo headless
    driver = webdriver.Chrome(options=options)

    # Navegar a la página
    driver.get(url)

    # Buscar la lista de especies
    species_list = driver.find_element(By.CLASS_NAME, 'speciesList')
    all_species = species_list.find_elements(By.CLASS_NAME, 'speciesNewRow')

    # Crear un archivo CSV para almacenar los datos
    with open("captus.csv", mode="w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["Nombre de la Especie", "Enlace"])

        # Extraer información de cada especie
        for specie in all_species:
            name = specie.find_element(By.CLASS_NAME, 'speciesTitle').text
            link = specie.find_element(By.CLASS_NAME, 'speciesTitle').get_attribute('href')
            print(f"Especie: {name}, Enlace: {link}")
            writer.writerow([name, link])  # Guardar en el CSV

    # Cerrar el navegador
    driver.quit()


    #class="elementor-loop-container elementor-grid"

# Llamar a la función para hacer el scraping
scrape_with_selenium(cnn_url)