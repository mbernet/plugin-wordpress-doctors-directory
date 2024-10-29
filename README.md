# Directorio de Profesionales

Crea un directorio de profesionales con filtrado por género y ubicación, mostrando la distancia desde la ubicación del usuario. Personaliza el radio de búsqueda, habilita la depuración y añade un disclaimer con citas inspiradoras.

## Características

- Filtrado por género (Masculino, Femenino, Otro).
- Geocodificación de la dirección del usuario mediante Google Maps API.
- Cálculo y visualización de la distancia desde el usuario a cada profesional.
- Personalización del radio de búsqueda.
- Modo de depuración para mostrar información adicional en el frontend.
- Inclusión de un disclaimer con una cita famosa.

## Instalación

1. Sube la carpeta del plugin `directorio-profesionales` al directorio `/wp-content/plugins/`.
2. Activa el plugin desde el panel de administración de WordPress.
3. Ve a **Ajustes > Directorio de Profesionales** para configurar el plugin.

## Configuración

1. **Clave API de Google Maps:**
    - Introduce tu clave API de Google Maps para habilitar la geocodificación.

2. **Radio de Búsqueda:**
    - Define el radio de búsqueda en kilómetros para filtrar profesionales cercanos.

3. **Modo de Depuración:**
    - Activa esta opción para ver información adicional en el frontend, útil para depuración.
 

## Uso

- Utiliza el shortcode `[directorio_profesionales]` en cualquier página o entrada para mostrar el directorio de profesionales.

## Personalización de Páginas Individuales con Elementor

1. Asegúrate de tener **Elementor Pro** instalado y activo.
2. Ve a **Elementor > Theme Builder** y crea un nuevo template **Single** para el tipo de post `profesional`.
3. Diseña el layout utilizando los widgets dinámicos de Elementor para mostrar los campos personalizados.

## Soporte

Para soporte, por favor apañatelas como puedas

## Licencia

Este plugin está licenciado bajo la GPL2.