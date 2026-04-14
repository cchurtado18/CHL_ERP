# 📋 Módulo de Tracking con Temporizador - CH Logistics ERP

## 🎯 Descripción

El módulo de Tracking es un sistema profesional de seguimiento con temporizadores que permite gestionar tareas, recordatorios y notificaciones automáticas. Cada tracking tiene un temporizador configurable que envía notificaciones cuando vence el tiempo asignado.

## ✨ Características Principales

### 🕐 **Temporizador Inteligente**
- Configuración de duración personalizable (1-720 horas)
- Vista previa en tiempo real del tiempo restante
- Actualización automática cada minuto
- Alertas visuales cuando vence el tiempo

### 🔔 **Sistema de Notificaciones**
- Notificaciones automáticas al vencer el temporizador
- Notificaciones en tiempo real en la navbar
- Historial completo de notificaciones
- Marcado automático como "vencido"

### 📊 **Dashboard Profesional**
- Estadísticas en tiempo real
- Tarjetas informativas con métricas
- Búsqueda rápida por código
- Filtros avanzados

### 🎨 **Interfaz Moderna**
- Diseño responsive con Bootstrap 5
- Iconografía FontAwesome
- Colores intuitivos por estado
- Animaciones suaves

## 🚀 Funcionalidades

### 1. **Dashboard de Tracking**
- **Ruta:** `/tracking/dashboard`
- **Descripción:** Vista principal con estadísticas y trackings próximos a vencer
- **Características:**
  - Tarjetas de estadísticas (Total, Pendientes, Vencidos, Completados)
  - Búsqueda rápida por código
  - Lista de próximos a vencer (7 días)
  - Temporizadores activos en tiempo real

### 2. **Crear Nuevo Tracking**
- **Ruta:** `/tracking/crear`
- **Descripción:** Formulario profesional para crear trackings con temporizador
- **Características:**
  - Selección de cliente
  - Generación automática de código
  - Configuración de duración (1-720 horas)
  - Vista previa del temporizador
  - Validaciones en tiempo real

### 3. **Lista de Trackings**
- **Ruta:** `/tracking`
- **Descripción:** Gestión completa de todos los trackings
- **Características:**
  - Filtros avanzados (Estado, Cliente, Fecha)
  - Temporizadores en tiempo real
  - Cambio de estado dinámico
  - Exportación (en desarrollo)
  - Paginación

### 4. **Gestión de Estados**
- **Estados disponibles:**
  - 🟡 **Pendiente:** Tarea por iniciar
  - 🔵 **En Proceso:** Tarea en ejecución
  - 🟢 **Completado:** Tarea finalizada
  - 🔴 **Vencido:** Tiempo agotado
  - ⚫ **Cancelado:** Tarea cancelada

## 🔧 Configuración del Sistema

### 1. **Comando Automático**
Para verificar recordatorios automáticamente, configura un cron job:

```bash
# Agregar al crontab (cada 5 minutos)
*/5 * * * * cd /path/to/your/project && php artisan tracking:verificar-recordatorios
```

### 2. **Ejecución Manual**
```bash
# Verificar recordatorios manualmente
php artisan tracking:verificar-recordatorios

# Crear datos de ejemplo
php artisan db:seed --class=TrackingSeeder
```

## 📱 Uso del Sistema

### **Crear un Tracking**

1. **Acceder al Dashboard**
   - Ve a `/tracking/dashboard`
   - Haz clic en "Nuevo Tracking"

2. **Configurar el Tracking**
   - Selecciona el cliente
   - El código se genera automáticamente
   - Configura la duración del temporizador
   - Establece la fecha y hora del recordatorio
   - Agrega notas adicionales

3. **Vista Previa**
   - El sistema muestra el tiempo restante en tiempo real
   - Valida que la fecha sea futura
   - Confirma la creación

### **Monitorear Trackings**

1. **Dashboard Principal**
   - Revisa las estadísticas generales
   - Ve trackings próximos a vencer
   - Usa la búsqueda rápida

2. **Lista Completa**
   - Aplica filtros según necesidades
   - Cambia estados dinámicamente
   - Monitorea temporizadores en tiempo real

3. **Notificaciones**
   - Recibe alertas automáticas
   - Revisa el dropdown en la navbar
   - Accede al módulo de notificaciones

## 🔄 Flujo de Trabajo

### **Flujo Típico:**

1. **Creación** → Tracking creado con temporizador
2. **Monitoreo** → Seguimiento en tiempo real
3. **Notificación** → Alerta automática al vencer
4. **Actualización** → Cambio de estado según progreso
5. **Completado** → Tarea finalizada

### **Estados del Temporizador:**

- **🟢 Activo:** Tiempo restante visible
- **🟡 Próximo a vencer:** Menos de 24 horas
- **🔴 Vencido:** Tiempo agotado, notificación enviada

## 📊 API Endpoints

### **Rutas Principales:**
```php
GET    /tracking/dashboard          # Dashboard principal
GET    /tracking                    # Lista de trackings
GET    /tracking/crear              # Formulario de creación
POST   /tracking                    # Crear tracking
GET    /tracking/{id}               # Ver detalles
GET    /tracking/{id}/editar        # Formulario de edición
PUT    /tracking/{id}               # Actualizar tracking
DELETE /tracking/{id}               # Eliminar tracking
```

### **Rutas Especializadas:**
```php
POST   /tracking/{id}/actualizar-estado    # Cambiar estado
GET    /tracking/buscar                    # Buscar por código
GET    /tracking/proximos-vencer           # Próximos a vencer
GET    /tracking/verificar-recordatorios   # Verificar vencidos
```

## 🎨 Personalización

### **Colores por Estado:**
- **Pendiente:** `warning` (amarillo)
- **En Proceso:** `info` (azul)
- **Completado:** `success` (verde)
- **Vencido:** `danger` (rojo)
- **Cancelado:** `secondary` (gris)

### **Iconos:**
- **Dashboard:** `fas fa-chart-line`
- **Tracking:** `fas fa-stopwatch`
- **Temporizador:** `fas fa-clock`
- **Vencido:** `fas fa-exclamation-triangle`
- **Completado:** `fas fa-check-circle`

## 🔍 Búsqueda y Filtros

### **Filtros Disponibles:**
- **Estado:** Pendiente, En Proceso, Completado, Vencido, Cancelado
- **Cliente:** Filtro por cliente específico
- **Fecha:** Hoy, Esta semana, Este mes, Ya venció

### **Búsqueda Rápida:**
- Búsqueda por código de tracking
- Resultados en tiempo real
- Información detallada del resultado

## 📈 Métricas y Estadísticas

### **Dashboard Metrics:**
- **Total Trackings:** Número total de seguimientos
- **Pendientes:** Trackings por iniciar
- **Vencidos:** Trackings con tiempo agotado
- **Completados:** Trackings finalizados exitosamente

### **Próximos a Vencer:**
- Trackings que vencen en los próximos 7 días
- Contador visual
- Acciones rápidas (Ver, Completar)

## 🔔 Sistema de Notificaciones

### **Tipos de Notificaciones:**
1. **Recordatorio de Tracking:** Al crear un tracking
2. **Tracking Vencido:** Cuando vence el temporizador
3. **Estado Actualizado:** Al cambiar el estado

### **Configuración:**
- Notificaciones automáticas para todos los usuarios
- Almacenamiento en base de datos
- Interfaz de gestión completa

## 🛠️ Mantenimiento

### **Comandos Útiles:**
```bash
# Verificar recordatorios
php artisan tracking:verificar-recordatorios

# Crear datos de ejemplo
php artisan db:seed --class=TrackingSeeder

# Limpiar cache
php artisan cache:clear
php artisan config:clear
```

### **Logs:**
- Los comandos generan logs informativos
- Errores se registran automáticamente
- Monitoreo de rendimiento disponible

## 🚀 Próximas Mejoras

### **Funcionalidades Planificadas:**
- [ ] Exportación a Excel/PDF
- [ ] Notificaciones por email
- [ ] Integración con calendario
- [ ] Reportes avanzados
- [ ] API REST completa
- [ ] Aplicación móvil

### **Optimizaciones:**
- [ ] Cache de consultas frecuentes
- [ ] Paginación infinita
- [ ] Búsqueda con autocompletado
- [ ] Drag & drop para estados

---

## 📞 Soporte

Para soporte técnico o consultas sobre el módulo de Tracking:

- **Documentación:** Este archivo README
- **Código:** Revisar controladores y vistas
- **Base de datos:** Verificar migraciones y seeders

---

**Desarrollado con ❤️ para CH Logistics ERP** 