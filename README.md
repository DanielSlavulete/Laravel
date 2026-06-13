<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Membership Management System

Aplicación web desarrollada con **Laravel**, **Filament** y **PostgreSQL(Supabase)** para la gestión integral de socios, solicitudes y cuotas de una asociación.

La plataforma recibe información desde un sitio web externo desarrollado en WordPress mediante una API REST. Cuando un usuario completa el formulario de inscripción, sus datos son almacenados automáticamente como una **solicitud pendiente** dentro del sistema.

A través del panel de administración construido con Filament, los administradores pueden:

* Revisar solicitudes recibidas desde la web.
* Aprobar o rechazar solicitudes de nuevos socios.
* Convertir automáticamente solicitudes aprobadas en registros de socios.
* Crear, editar, eliminar y gestionar socios manualmente.
* Filtrar, ordenar y buscar información de forma eficiente.
* Gestionar cuotas anuales y controlar su estado de pago.
* Importar socios y cuotas desde archivos CSV.
* Exportar datos a CSV para migraciones, copias de seguridad o análisis en herramientas como Excel.
* Administrar toda la información desde una interfaz moderna, rápida e intuitiva.

## Architecture

* **Backend:** Laravel
* **Admin Panel:** Filament
* **Database:** Supabase (PostgreSQL)
* **Frontend Integration:** WordPress → API REST → Laravel
* **Views:** Blade + Filament Components

## Workflow

1. Un usuario completa el formulario de inscripción en la web.
2. WordPress envía los datos a la API de Laravel.
3. El sistema crea una solicitud pendiente.
4. Un administrador revisa la solicitud.
5. Si es aprobada, se crea automáticamente un nuevo socio.
6. El socio puede ser gestionado junto con sus cuotas y documentación asociada.

## Features

✅ Gestión de solicitudes

✅ Gestión de socios

✅ Control de cuotas anuales

✅ Importación CSV

✅ Exportación CSV

✅ API REST

✅ Integración con WordPress

✅ PostgreSQL (Supabase)

✅ Panel administrativo con Filament

✅ Búsqueda, filtrado y ordenación avanzada

✅ Validación y sanitización de datos  

✅ Rate Limiting para prevención de spam y abuso

Este proyecto ha sido diseñado para simplificar la administración de una asociacion, centralizando en una única plataforma la gestión de inscripciones, socios y pagos.
