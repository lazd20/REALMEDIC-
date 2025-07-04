# Sistema de Gestión de Ingresos - Realmedic

Este proyecto es un sistema de control de ingresos hospitalarios desarrollado en PHP y MySQL, orientado a la gestión de pacientes, habitaciones, evolución clínica y altas médicas en una clínica.

## 📁 Estructura de Archivos

- `login.php` / `logout.php`: Autenticación de usuarios con control de sesión.
- `index.php`: Página de inicio protegida por login.
- `usuarios.php`: Gestión de usuarios con roles (`admin`, `editor`, `viewer`).
- `registrar.php`: Registro de nuevos ingresos hospitalarios (emergencias, cirugías, etc).
- `panel_ingresos.php`: Panel principal para visualizar, modificar o dar de alta ingresos.
- `modificar_ingreso.php`: Edición de información de pacientes ingresados.
- `dar_alta.php`: Registro de altas médicas.
- `reporte_altas.php`: Reporte de pacientes dados de alta.
- `guardar_evolucion.php`: Registro de evolución u observaciones médicas.
- `habitaciones_libres.php`: Consulta de habitaciones disponibles con descripción.

## ⚙️ Requisitos

- PHP >= 7.4
- MySQL / MariaDB
- Servidor web (Apache recomendado)
- Composer (opcional para futuras integraciones)

## 🔐 Control de Acceso

El sistema incluye control de roles:

- `admin`: Acceso total a usuarios, registros e informes.
- `editor`: Puede registrar y modificar ingresos, y ver reportes.
- `viewer`: Solo puede visualizar datos.

## 🏥 Funcionalidades Clave

- Registro de ingresos hospitalarios (con médico tratante, motivo, habitación, fechas).
- Alta médica con control de historial.
- Evoluciones médicas cronológicas por ingreso.
- Filtro por habitaciones disponibles.
- Reporte de pacientes dados de alta.
- Sistema de login con control de acceso por roles.

## 🚀 Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/tu_usuario/nombre_repositorio.git
