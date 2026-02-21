# Las Sevillanas - Plataforma Web (E-commerce)

> [cite_start]Aplicación web integral para comercio electrónico construida bajo una arquitectura modular MVC (Modelo-Vista-Controlador) distribuida en tres niveles: presentación, negocio y datos[cite: 1100]. [cite_start]El sistema permite la gestión de catálogo, carrito de compras, aplicación dinámica de promociones y pagos seguros en línea[cite: 1204, 1207, 1210, 1492.

## 🚀 Tecnologías y Stack

**Backend & Base de Datos**
* **Lenguaje:** PHP 8.2
* **Base de Datos:** MariaDB/MySQL 8.0 gestionada con PDO y sentencias preparadas.
* **Gestor de dependencias:** Composer

**Frontend**
* **Estructura y Diseño:** HTML5, CSS3 responsivo (adaptable a dispositivos móviles mediante Media Queries).
* **Lógica e Interacción:** JavaScript ES6+ modularizado, jQuery 3.x.
* **Frameworks UI:** Bootstrap 5, SweetAlert2.

**Integraciones y Librerías Externas**
* **Stripe SDK (`stripe/stripe-php`):** Procesamiento seguro de pagos mediante tarjetas bancarias.
* **PHPMailer:** Envío automatizado de correos electrónicos transaccionales.
* **Dompdf:** Generación dinámica de recibos y comprobantes en formato PDF.
* **vlucas/phpdotenv:** Gestión segura de variables de entorno y credenciales.

**Infraestructura y Despliegue**
* **Servidor Nube:** Instancia AWS EC2 (Ubuntu Server 24.04 LTS).
* **Servidor Web:** Apache 2.4.
* **Seguridad y Redes:** Cloudflare Tunnel para conexiones seguras a la base de datos y Certbot (Let's Encrypt) para certificados SSL/HTTPS.

## 📋 Características Principales

* **Gestión de Inventario y Carrito:** Control de stock en tiempo real protegido mediante Triggers en SQL para evitar ventas sin inventario.
* **Motor de Promociones:** Sistema automatizado de descuentos y cupones validado a nivel base de datos (restricciones CHECK) e integrado en el flujo de checkout.
* **Seguridad Robusta:** Autenticación de usuarios con cifrado `Argon2id` (estándar OWASP), protección CSRF, y blindaje contra inyecciones SQL mediante PDO.
* **Despliegue Híbrido Seguro:** Conexión segura entre la base de datos local y el servidor web en AWS mediante túneles Cloudflare, sin exponer puertos públicos.

## 🛠️ Instalación y Configuración Local

### Prerrequisitos
* PHP 8.2+ y Composer instalados.
* Servidor MySQL/MariaDB 8.0.
* Cuenta de desarrollador en Stripe (para API Keys).

### Pasos para ejecutar

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/AlfredoDeAlba/LasSevillanasPW.git](https://github.com/AlfredoDeAlba/LasSevillanasPW.git)
   cd LasSevillanasPW
2. Configurar la Base de Datos:
    Crea una base de datos llamada Sevillanas con codificación utf8mb4_unicode_ci.
    Importa el script SQL incluido en el proyecto (db/sevillanas_master-database_v2025.sql)

3. Configurar Variables de Entorno:
    Crea un archivo .env en la raíz del proyecto basándote en la estructura requerida.
    Añade tus credenciales de base de datos (DB_HOST, DB_NAME, DB_USER, DB_PASS) y las claves de Stripe (STRIPE_SECRET_KEY, STRIPE_PUBLISHABLE_KEY).
4. Ejecutar el servidor local:
    Despliega el proyecto en tu entorno de servidor local (ej. XAMPP, LAMP, o servidor integrado de PHP) apuntando al archivo index.php.
   
