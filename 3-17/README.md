# Actividad 3.17 – Control de acceso en el servidor + HTTPS

La protección la hace **el servidor web**, no PHP: Apache/NGINX pide las
credenciales y, hasta que no son válidas, no ejecuta `index.php`.

```
3-17/
├── seguro/index.php        → se copia a /var/www/html/seguro/
├── apache/seguro-ssl.conf  → VirtualHost 80 (redirige) + 443 (SSL + Digest)
├── nginx/seguro.conf       → alternativa NGINX (auth_basic)
└── instalar_apache.sh      → hace todos los pasos de Apache automáticamente
```

## Opción A – Apache (AuthType Digest)

Automático: `sudo ./instalar_apache.sh alumno` (pide la contraseña del usuario).

Paso a paso:

```bash
# 1. Directorio protegido
sudo mkdir -p /var/www/html/seguro
sudo cp seguro/index.php /var/www/html/seguro/

# 2. Fichero de usuarios Digest. El realm debe coincidir con AuthName.
sudo apt install apache2-utils
sudo htdigest -c /etc/apache2/.htdigest "Zona Segura" alumno
sudo chown root:www-data /etc/apache2/.htdigest && sudo chmod 640 /etc/apache2/.htdigest

# 3. Módulos: autenticación Digest y SSL
sudo a2enmod auth_digest ssl

# 4. Certificado autofirmado (cualquiera de los dos)
sudo make-ssl-cert generate-default-snakeoil --force-overwrite
#   o bien:
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
     -keyout /etc/ssl/private/seguro.key -out /etc/ssl/certs/seguro.crt \
     -subj "/C=ES/ST=Local/O=Tema3/CN=localhost"

# 5. VirtualHost en el 443
sudo cp apache/seguro-ssl.conf /etc/apache2/sites-available/
sudo a2dissite 000-default
sudo a2ensite seguro-ssl
sudo apache2ctl configtest && sudo systemctl reload apache2
```

Bloque clave del VirtualHost:

```apache
<Directory /var/www/html/seguro>
    AuthType Digest
    AuthName "Zona Segura"
    AuthDigestProvider file
    AuthUserFile /etc/apache2/.htdigest
    Require valid-user
</Directory>
```

## Opción B – NGINX (auth_basic)

```bash
sudo apt install nginx php8.1-fpm apache2-utils ssl-cert
sudo htpasswd -c /etc/nginx/.htpasswd alumno
sudo make-ssl-cert generate-default-snakeoil --force-overwrite
sudo mkdir -p /var/www/html/seguro && sudo cp seguro/index.php /var/www/html/seguro/
sudo cp nginx/seguro.conf /etc/nginx/sites-available/seguro
sudo ln -s /etc/nginx/sites-available/seguro /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

> Apache y NGINX no pueden escuchar a la vez en los puertos 80/443: usa uno de los dos.

## Comprobaciones

| Prueba | Resultado esperado |
|---|---|
| `curl -i http://localhost/seguro/` | `301` → `https://localhost/seguro/` |
| `curl -k -i https://localhost/seguro/` | `401` + `WWW-Authenticate: Digest realm="Zona Segura"…` |
| `curl -k -i --digest -u alumno:mala https://localhost/seguro/` | `401` |
| `curl -k -i --digest -u alumno:CLAVE https://localhost/seguro/` | `200`, la tabla de `index.php` |
| `openssl s_client -connect localhost:443 </dev/null` | muestra el certificado autofirmado |

(En NGINX usa `-u` sin `--digest`, porque es autenticación Basic.)

En el navegador: `https://localhost/seguro/` muestra primero el aviso del
certificado autofirmado (Avanzado → Aceptar el riesgo) y luego el cuadro de
usuario/contraseña. Con la credencial correcta, la página indica
**HTTPS: SÍ**, el tipo **Digest** y la versión de TLS.

Para comprobar que PHP **no** se ejecuta antes de autenticar: añade
`file_put_contents('/tmp/ejecutado.txt', date('c'));` al principio de
`index.php`, haz la petición sin credenciales y verás que el fichero no se crea.

## Diferencia Digest / Basic

- **Basic**: envía `usuario:contraseña` en Base64 (reversible) → obligatorio HTTPS.
- **Digest**: envía un hash MD5 con un *nonce* del servidor; la contraseña no
  viaja, pero MD5 es débil, así que igualmente se combina con HTTPS.
