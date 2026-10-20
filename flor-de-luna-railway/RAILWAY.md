# Publicar Flor de Luna en Railway (PHP + MySQL)

El sitio y la base de datos viven en **un mismo proyecto de Railway**, con dos servicios:

| Servicio | Qué es |
|---|---|
| **MySQL** | La base de datos (Railway la crea por ti) |
| **flor-de-luna** | Este repositorio, que Railway construye con el `Dockerfile` (PHP 8.3 + Apache) |

> Vercel no ejecuta PHP. Esta versión es para Railway; la de Vercel puede seguir como demostración estática.

## 1. Sube esta carpeta a GitHub
Usa **otra rama** (por ejemplo `railway`) o un repositorio nuevo, para no mezclarla con la versión estática de `main`.

## 2. Crea el proyecto en Railway
1. En [railway.com](https://railway.com) → **New Project** → **Provision MySQL** (o *Database → Add MySQL*).
2. En el mismo proyecto: **+ New** → **GitHub Repo** → elige `Flor-de-Luna` y la rama `railway`.
   Railway detecta el `Dockerfile` y lo construye.

## 3. Conecta el sitio con MySQL (variables)
En el servicio **flor-de-luna** → pestaña **Variables** → **New Variable** → **Add Reference** y agrega estas cinco, apuntando al servicio MySQL:

| Variable | Valor (referencia) |
|---|---|
| `MYSQLHOST` | `${{MySQL.MYSQLHOST}}` |
| `MYSQLPORT` | `${{MySQL.MYSQLPORT}}` |
| `MYSQLUSER` | `${{MySQL.MYSQLUSER}}` |
| `MYSQLPASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `MYSQLDATABASE` | `${{MySQL.MYSQLDATABASE}}` |

(Si tu servicio MySQL se llama distinto de `MySQL`, cambia ese nombre dentro de `${{ }}`.)
Alternativa: una sola variable `MYSQL_URL` = `${{MySQL.MYSQL_URL}}`.

Al guardar, Railway vuelve a desplegar.

## 4. Las tablas se crean solas
Al arrancar, `start.sh` ejecuta `Conection/migrar.php`, que corre `Base/flor_de_luna.sql` (crea las 8 tablas y los 8 perfumes). Es seguro: no borra datos. Lo verás en **Deployments → View logs**:

```
[migrar] Listo. Tablas: 8 (carrito, categorias, ...). Productos: 8
```

## 5. Abre el sitio
En el servicio **flor-de-luna** → **Settings → Networking → Generate Domain**. Abre la dirección que te da.

Comprobación de la conexión: `https://TU-DOMINIO/Conection/salud.php` debe responder:

```json
{"ok":true,"base":"railway","host":"...","tablas":8,"productos":8}
```

## Ver los datos
En el servicio **MySQL** → pestaña **Data**, o conéctate con DBeaver / MySQL Workbench usando los datos de la pestaña **Connect** (host y puerto públicos).

## Probar en tu PC
- **XAMPP:** no cambies nada; sin variables usa `127.0.0.1`, `root`, sin contraseña y la base `flor_de_luna` (impórtala en phpMyAdmin).
- **Contra la base de Railway desde tu PC:** define las mismas variables con el host y puerto **públicos** de la pestaña *Connect*.

## Si algo falla
| Síntoma | Qué revisar |
|---|---|
| `salud.php` dice `ok:false` | Las 5 variables del paso 3 (nombre del servicio, sin espacios) |
| El sitio abre pero registro/login fallan | Abre `salud.php`; revisa los logs del servicio |
| "Application failed to respond" | Que el `Dockerfile` esté en la raíz del repositorio |
| El plan de prueba de Railway se agotó | Railway pausa los servicios; hay que activar un plan |
