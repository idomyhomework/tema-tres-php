#!/usr/bin/env bash
# Actividad 3.17 — despliegue automático en Apache (ejecutar con sudo desde esta carpeta)
#   sudo ./instalar_apache.sh [usuario]
set -euo pipefail

USUARIO="${1:-alumno}"
REALM="Zona Segura"
DIR="$(cd "$(dirname "$0")" && pwd)"

if [[ $EUID -ne 0 ]]; then
    echo "Ejecuta este script con sudo." >&2
    exit 1
fi

echo "==> 1. Directorio protegido /var/www/html/seguro/"
mkdir -p /var/www/html/seguro
cp "$DIR/seguro/index.php" /var/www/html/seguro/index.php
chown -R www-data:www-data /var/www/html/seguro

echo "==> 2. Usuarios Digest (htdigest) para el realm \"$REALM\""
apt-get install -y apache2-utils ssl-cert >/dev/null
if [[ -f /etc/apache2/.htdigest ]]; then
    htdigest /etc/apache2/.htdigest "$REALM" "$USUARIO"
else
    htdigest -c /etc/apache2/.htdigest "$REALM" "$USUARIO"
fi
chown root:www-data /etc/apache2/.htdigest
chmod 640 /etc/apache2/.htdigest

echo "==> 3. Certificado autofirmado (make-ssl-cert)"
make-ssl-cert generate-default-snakeoil --force-overwrite

echo "==> 4. Módulos y sitio"
a2enmod ssl auth_digest
cp "$DIR/apache/seguro-ssl.conf" /etc/apache2/sites-available/seguro-ssl.conf
a2dissite 000-default default-ssl >/dev/null 2>&1 || true
a2ensite seguro-ssl

echo "==> 5. Comprobar y recargar"
apache2ctl configtest
systemctl reload apache2

echo
echo "Listo. Prueba:"
echo "  curl -k -i https://localhost/seguro/                                   # 401"
echo "  curl -k -i --digest -u $USUARIO:TU_CLAVE https://localhost/seguro/       # 200"
echo "  curl -i http://localhost/seguro/                                       # 301 → https"
