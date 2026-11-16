# Actividad 3.12 – Endpoint con Autenticación Básica HTTP + HTTPS

`api.php` es un endpoint JSON que:

1. Rechaza con **403** cualquier petición que no llegue por HTTPS (la Basic Auth
   envía `usuario:contraseña` solo en Base64, sin cifrar).
2. Si faltan o son incorrectas las credenciales responde **401** con
   `WWW-Authenticate: Basic realm="API Tema 3"`.
3. Si son correctas devuelve **200** con datos JSON.

Credenciales de prueba: `api` / `secreto123`.

## 1. Habilitar HTTPS en Apache (certificado autofirmado)

```bash
sudo apt install ssl-cert            # trae make-ssl-cert y el certificado "snakeoil"
sudo make-ssl-cert generate-default-snakeoil --force-overwrite
sudo a2enmod ssl
sudo a2ensite default-ssl            # VirtualHost en el puerto 443
sudo systemctl reload apache2
```

Con openssl, en lugar de `make-ssl-cert`:

```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/tema3.key -out /etc/ssl/certs/tema3.crt \
  -subj "/CN=localhost"
```

y en `/etc/apache2/sites-available/default-ssl.conf`:

```apache
SSLCertificateFile    /etc/ssl/certs/tema3.crt
SSLCertificateKeyFile /etc/ssl/private/tema3.key
```

> Si PHP corre como **PHP-FPM** (no mod_php), Apache no pasa la cabecera
> `Authorization` a PHP por defecto. Añade en el VirtualHost o en un `.htaccess`:
> `CGIPassAuth On`

## 2. Pruebas de acceso

Ajusta la ruta a donde tengas publicada la carpeta (`-k` acepta el certificado autofirmado):

```bash
# Sin credenciales → 401 + cabecera WWW-Authenticate
curl -k -i https://localhost/Tema3/3-12/api.php

# Credenciales incorrectas → 401
curl -k -i -u api:mala https://localhost/Tema3/3-12/api.php

# Credenciales correctas → 200 + JSON
curl -k -i -u api:secreto123 https://localhost/Tema3/3-12/api.php

# Por HTTP plano → 403 (se exige HTTPS)
curl -i -u api:secreto123 http://localhost/Tema3/3-12/api.php

# Ver qué viaja realmente: la cabecera es solo Base64
echo -n 'api:secreto123' | base64      # YXBpOnNlY3JldG8xMjM=
curl -k -i -H 'Authorization: Basic YXBpOnNlY3JldG8xMjM=' https://localhost/Tema3/3-12/api.php
```

En el navegador, `https://localhost/Tema3/3-12/api.php` muestra el aviso del
certificado autofirmado y después el cuadro modal de usuario/contraseña.

## 3. Redirigir HTTP → HTTPS (opcional)

En el VirtualHost del puerto 80:

```apache
Redirect permanent / https://localhost/
```
