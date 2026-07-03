#!/usr/bin/env bash
set -euo pipefail
trap 'printf "ERROR line %s: %s\n" "$LINENO" "$BASH_COMMAND" >&2' ERR

# Creates a limited trainee access for the Oneduc dev server:
# - WireGuard peer dedicated to the trainee
# - Linux user without sudo and with SSH key auth only
# - Separate Laravel workspace on an Apache port
# - Firewall rules limiting the trainee VPN IP to SSH and the trainee web port

STUDENT_USER="${STUDENT_USER:-stagiaire}"
STUDENT_IP="${STUDENT_IP:-10.8.0.3}"
STUDENT_CIDR="${STUDENT_CIDR:-${STUDENT_IP}/32}"
APP_DIR="${APP_DIR:-/var/www/Oneduc_Stagiaire}"
APP_PORT="${APP_PORT:-8091}"
SOURCE_DIR="${SOURCE_DIR:-/var/www/Oneduc_Dev}"
OWNER_USER="${OWNER_USER:-laurents}"
ACCESS_DIR="${ACCESS_DIR:-/home/${OWNER_USER}/stagiaire-access}"
WG_ENDPOINT="${WG_ENDPOINT:-louises.fr:51820}"
WG_CONF="${WG_CONF:-}"
SERVER_VPN_IP="${SERVER_VPN_IP:-}"
APACHE_SITE="oneduc-stagiaire-${APP_PORT}.conf"
FW_SCRIPT="/usr/local/sbin/oneduc-stagiaire-firewall.sh"
FW_SERVICE="/etc/systemd/system/oneduc-stagiaire-firewall.service"

die() {
    printf 'ERROR: %s\n' "$*" >&2
    exit 1
}

need_cmd() {
    command -v "$1" >/dev/null 2>&1 || die "Commande manquante: $1"
}

require_root() {
    if [ "$(id -u)" -ne 0 ]; then
        die "Lance ce script avec sudo: sudo bash $0"
    fi
}

first_wireguard_conf() {
    local conf

    for conf in $(find /etc/wireguard -maxdepth 1 -type f -name '*.conf' 2>/dev/null | sort); do
        if grep -Eq '^[[:space:]]*ListenPort[[:space:]]*=' "$conf" && ! grep -Eq '^[[:space:]]*Endpoint[[:space:]]*=' "$conf"; then
            printf '%s\n' "$conf"
            return 0
        fi
    done

    return 0
}

assert_wireguard_server_conf() {
    grep -Eq '^[[:space:]]*ListenPort[[:space:]]*=' "$WG_CONF" || die "${WG_CONF} ne ressemble pas a une config serveur WireGuard: ListenPort absent. Le peer stagiaire doit etre ajoute sur le serveur WireGuard, probablement louises.fr."
    if grep -Eq '^[[:space:]]*Endpoint[[:space:]]*=' "$WG_CONF"; then
        die "${WG_CONF} ressemble a une config cliente WireGuard: Endpoint present. Le peer stagiaire doit etre ajoute sur le serveur WireGuard, probablement louises.fr."
    fi
}

choose_free_student_ip() {
    local a b c d host candidate

    IFS=. read -r a b c d <<EOF
$STUDENT_IP
EOF
    for host in $(seq "$((d + 1))" 254); do
        candidate="${a}.${b}.${c}.${host}"
        if ! grep -Eq "^[[:space:]]*AllowedIPs[[:space:]]*=[[:space:]]*${candidate//./\.}/32([[:space:],#]|$)" "$WG_CONF"; then
            STUDENT_IP="$candidate"
            STUDENT_CIDR="${candidate}/32"
            printf 'Using next free WireGuard IP: %s\n' "$STUDENT_CIDR"
            return 0
        fi
    done

    die "Aucune IP libre trouvee apres ${STUDENT_IP} dans le /24 WireGuard"
}

existing_peer_cidr() {
    local wg_public="$1"

    awk -v key="$wg_public" '
        /^\[Peer\]$/ {
            if (in_peer && found && allowed != "") {
                print allowed
                exit
            }
            in_peer = 1
            found = 0
            allowed = ""
            next
        }
        in_peer && /^[[:space:]]*PublicKey[[:space:]]*=/ {
            value = $0
            sub(/^[^=]*=/, "", value)
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", value)
            if (value == key) found = 1
        }
        in_peer && /^[[:space:]]*AllowedIPs[[:space:]]*=/ {
            value = $0
            sub(/^[^=]*=/, "", value)
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", value)
            split(value, parts, ",")
            gsub(/^[[:space:]]+|[[:space:]]+$/, "", parts[1])
            allowed = parts[1]
        }
        END {
            if (in_peer && found && allowed != "") print allowed
        }
    ' "$WG_CONF" | head -n 1
}

use_student_cidr() {
    STUDENT_CIDR="$1"
    STUDENT_IP="${STUDENT_CIDR%%/*}"
}

ensure_line() {
    local line="$1"
    local file="$2"

    grep -Fxq "$line" "$file" 2>/dev/null || printf '%s\n' "$line" >> "$file"
}

append_wireguard_peer() {
    local wg_public="$1"

    if grep -Fq "PublicKey = ${wg_public}" "$WG_CONF"; then
        printf 'WireGuard peer already present in %s\n' "$WG_CONF"
        return
    fi

    if grep -Eq "^[[:space:]]*AllowedIPs[[:space:]]*=[[:space:]]*${STUDENT_CIDR//./\\.}([[:space:],#]|$)" "$WG_CONF"; then
        die "L'IP WireGuard ${STUDENT_CIDR} est deja presente dans ${WG_CONF}"
    fi

    {
        printf '\n'
        printf '[Peer]\n'
        printf '# %s - Oneduc trainee access - created %s\n' "$STUDENT_USER" "$(date -Iseconds)"
        printf 'PublicKey = %s\n' "$wg_public"
        printf 'AllowedIPs = %s\n' "$STUDENT_CIDR"
    } >> "$WG_CONF"
}

write_firewall_script() {
    local wg_iface="$1"

    install -m 0755 /dev/null "$FW_SCRIPT"
    cat > "$FW_SCRIPT" <<EOF
#!/usr/bin/env bash
set -euo pipefail

WG_IFACE="${wg_iface}"
STUDENT_IP="${STUDENT_IP}"
APP_PORT="${APP_PORT}"

insert_rule_once() {
    local chain="\$1"
    local pos="\$2"
    shift 2

    if ! iptables -C "\$chain" "\$@" 2>/dev/null; then
        iptables -I "\$chain" "\$pos" "\$@"
    fi
}

insert_rule_once FORWARD 1 -s "\$STUDENT_IP" -j REJECT
insert_rule_once INPUT 1 -i "\$WG_IFACE" -s "\$STUDENT_IP" -j REJECT
insert_rule_once INPUT 1 -i "\$WG_IFACE" -s "\$STUDENT_IP" -p icmp -j ACCEPT
insert_rule_once INPUT 1 -i "\$WG_IFACE" -s "\$STUDENT_IP" -p tcp --dport 22 -j ACCEPT
insert_rule_once INPUT 1 -i "\$WG_IFACE" -s "\$STUDENT_IP" -p tcp --dport "\$APP_PORT" -j ACCEPT
EOF

    cat > "$FW_SERVICE" <<EOF
[Unit]
Description=Restrict Oneduc trainee VPN access
After=network-online.target wg-quick@${wg_iface}.service
Wants=network-online.target

[Service]
Type=oneshot
ExecStart=${FW_SCRIPT}
RemainAfterExit=yes

[Install]
WantedBy=multi-user.target
EOF

    local service_name
    service_name="$(basename "$FW_SERVICE")"

    systemctl daemon-reload
    systemctl enable "$service_name" >/dev/null
    systemctl restart "$service_name"
    systemctl is-active --quiet "$service_name"
}

create_student_user() {
    if ! id "$STUDENT_USER" >/dev/null 2>&1; then
        useradd --create-home --shell /bin/bash --user-group "$STUDENT_USER"
    fi

    passwd -l "$STUDENT_USER" >/dev/null 2>&1 || true
    gpasswd -d "$STUDENT_USER" sudo >/dev/null 2>&1 || true
    gpasswd -d "$STUDENT_USER" www-data >/dev/null 2>&1 || true

    install -d -m 0700 -o "$STUDENT_USER" -g "$STUDENT_USER" "/home/${STUDENT_USER}/.ssh"
}

create_access_material() {
    local ssh_key="${ACCESS_DIR}/${STUDENT_USER}_oneduc_ed25519"
    local wg_private="${ACCESS_DIR}/${STUDENT_USER}_wireguard_private.key"
    local wg_public="${ACCESS_DIR}/${STUDENT_USER}_wireguard_public.key"

    install -d -m 0700 -o "$OWNER_USER" -g "$OWNER_USER" "$ACCESS_DIR"

    if [ ! -f "$ssh_key" ]; then
        ssh-keygen -t ed25519 -a 64 -N '' -C "${STUDENT_USER}-oneduc@$(hostname)" -f "$ssh_key" >/dev/null
    fi
    chown "$OWNER_USER:$OWNER_USER" "$ssh_key" "$ssh_key.pub"
    chmod 0600 "$ssh_key"
    chmod 0644 "$ssh_key.pub"

    if [ ! -f "$wg_private" ]; then
        umask 077
        wg genkey > "$wg_private"
        wg pubkey < "$wg_private" > "$wg_public"
    fi
    chown "$OWNER_USER:$OWNER_USER" "$wg_private" "$wg_public"
    chmod 0600 "$wg_private"
    chmod 0644 "$wg_public"

    cat "$wg_public"
}

configure_student_ssh_access() {
    local ssh_key="${ACCESS_DIR}/${STUDENT_USER}_oneduc_ed25519"
    local auth_line authorized_keys ssh_pub

    authorized_keys="/home/${STUDENT_USER}/.ssh/authorized_keys"
    ssh_pub="$(cat "$ssh_key.pub")"
    auth_line="from=\"${STUDENT_IP}\",no-agent-forwarding,no-X11-forwarding,no-port-forwarding ${ssh_pub}"
    if [ -f "$authorized_keys" ]; then
        awk -v key="$ssh_pub" 'index($0, key) == 0' "$authorized_keys" > "${authorized_keys}.tmp"
        cat "${authorized_keys}.tmp" > "$authorized_keys"
        rm -f "${authorized_keys}.tmp"
    fi
    printf '%s\n' "$auth_line" >> "$authorized_keys"
    chown "$STUDENT_USER:$STUDENT_USER" "$authorized_keys"
    chmod 0600 "$authorized_keys"
}

cleanup_invalid_wireguard_peer_reference() {
    local invalid_public_ref="${ACCESS_DIR}/${STUDENT_USER}_wireguard_public.key"
    local tmp

    grep -Fq "PublicKey = ${invalid_public_ref}" "$WG_CONF" || return 0
    printf 'Removing invalid WireGuard peer reference from %s...\n' "$WG_CONF"
    tmp="$(mktemp)"
    awk -v bad="${invalid_public_ref}" '
        /^\[Peer\]$/ {
            if (in_peer) {
                for (i = 1; i <= n; i++) print block[i]
            }
            in_peer = 1
            n = 1
            block[n] = $0
            skip = 0
            next
        }
        in_peer {
            block[++n] = $0
            if ($0 == "PublicKey = " bad) skip = 1
            next
        }
        { print }
        END {
            if (in_peer && !skip) {
                for (i = 1; i <= n; i++) print block[i]
            }
        }
    ' "$WG_CONF" > "$tmp"
    cat "$tmp" > "$WG_CONF"
    rm -f "$tmp"
}

prepare_app_workspace() {
    if [ ! -d "$APP_DIR" ]; then
        install -d -m 0750 -o "$STUDENT_USER" -g "$STUDENT_USER" "$APP_DIR"
    fi

    if [ ! -e "${APP_DIR}/artisan" ]; then
        if [ -d "${APP_DIR}/.git" ] || [ "$(find "$APP_DIR" -mindepth 1 -maxdepth 1 | wc -l)" -gt 0 ]; then
            printf 'Workspace %s existe deja et n est pas vide; clone ignore.\n' "$APP_DIR"
        else
            runuser -u "$STUDENT_USER" -- git config --global --add safe.directory "$SOURCE_DIR" || true
            runuser -u "$STUDENT_USER" -- git config --global --add safe.directory "${SOURCE_DIR}/.git" || true
            runuser -u "$STUDENT_USER" -- git clone --no-local "$SOURCE_DIR" "$APP_DIR"
        fi
    fi

    if [ -f "${APP_DIR}/.env.example" ] && [ ! -f "${APP_DIR}/.env" ]; then
        install -m 0640 -o "$STUDENT_USER" -g "$STUDENT_USER" "${APP_DIR}/.env.example" "${APP_DIR}/.env"
    fi

    if [ ! -f "${APP_DIR}/vendor/autoload.php" ]; then
        if [ -f "${SOURCE_DIR}/vendor/autoload.php" ]; then
            printf 'Copying PHP dependencies from source workspace...\n'
            cp -a "${SOURCE_DIR}/vendor" "${APP_DIR}/vendor"
        elif command -v composer >/dev/null 2>&1; then
            printf 'Installing PHP dependencies with composer...\n'
            runuser -u "$STUDENT_USER" -- sh -lc "cd '$APP_DIR' && composer install --no-interaction --prefer-dist"
        else
            die "vendor/autoload.php absent et composer indisponible; installe composer ou copie ${SOURCE_DIR}/vendor vers ${APP_DIR}/vendor"
        fi
    fi

    chown -R "$STUDENT_USER:$STUDENT_USER" "$APP_DIR"
    find "$APP_DIR" -type d -exec chmod 0750 {} +
    find "$APP_DIR" -type f -exec chmod 0640 {} +

    if [ -f "${APP_DIR}/artisan" ] && [ -f "${APP_DIR}/.env" ]; then
        if grep -Eq '^APP_KEY=$|^APP_KEY=[[:space:]]*$' "${APP_DIR}/.env"; then
            runuser -u "$STUDENT_USER" -- php "${APP_DIR}/artisan" key:generate --force >/dev/null
        fi
    fi

    if command -v setfacl >/dev/null 2>&1; then
        setfacl -R -m u:www-data:rwX "$APP_DIR"
        setfacl -R -m d:u:www-data:rwX "$APP_DIR"
        setfacl -m "u:${STUDENT_USER}:---" "$SOURCE_DIR" || true
    else
        printf 'WARN: setfacl absent; Apache peut manquer de droits sur %s.\n' "$APP_DIR" >&2
    fi
}

configure_apache() {
    need_cmd apache2ctl
    need_cmd a2ensite

    ensure_line "Listen ${APP_PORT}" /etc/apache2/ports.conf

    cat > "/etc/apache2/sites-available/${APACHE_SITE}" <<EOF
<VirtualHost *:${APP_PORT}>
    ServerName oneduc-stagiaire.local
    DocumentRoot ${APP_DIR}/public

    <Directory ${APP_DIR}/public>
        AllowOverride All
        Require ip 10.8.0.0/24 127.0.0.1
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/oneduc-stagiaire-${APP_PORT}-error.log
    CustomLog \${APACHE_LOG_DIR}/oneduc-stagiaire-${APP_PORT}-access.log combined
</VirtualHost>
EOF

    a2ensite "$APACHE_SITE" >/dev/null
    apache2ctl configtest
    systemctl reload apache2
}

write_client_config_and_readme() {
    local wg_private_file="${ACCESS_DIR}/${STUDENT_USER}_wireguard_private.key"
    local ssh_key="${ACCESS_DIR}/${STUDENT_USER}_oneduc_ed25519"
    local client_conf="${ACCESS_DIR}/${STUDENT_USER}-wireguard.conf"
    local readme="${ACCESS_DIR}/README-${STUDENT_USER}.txt"
    local wg_iface="$1"
    local server_public="$2"
    local server_vpn_ip="$3"

    cat > "$client_conf" <<EOF
[Interface]
PrivateKey = $(cat "$wg_private_file")
Address = ${STUDENT_CIDR}
DNS = 1.1.1.1

[Peer]
PublicKey = ${server_public}
AllowedIPs = ${server_vpn_ip}/32
Endpoint = ${WG_ENDPOINT}
PersistentKeepalive = 25
EOF

    cat > "$readme" <<EOF
Acces stagiaire Oneduc
=======================

Fichiers:
- WireGuard client: ${client_conf}
- Cle SSH privee: ${ssh_key}
- Cle SSH publique: ${ssh_key}.pub

Test apres connexion WireGuard:
  ssh -i ${ssh_key} ${STUDENT_USER}@${server_vpn_ip}
  http://${server_vpn_ip}:${APP_PORT}

Limites mises en place:
- utilisateur ${STUDENT_USER} sans sudo
- SSH uniquement depuis l IP VPN ${STUDENT_IP}
- WireGuard peer ${STUDENT_CIDR} sur ${wg_iface}
- firewall: ${STUDENT_IP} limite a SSH, ICMP et port ${APP_PORT}; pas de routage FORWARD
- workspace separe: ${APP_DIR}
- ACL: ${STUDENT_USER} ne peut pas traverser ${SOURCE_DIR}

Pour couper l acces rapidement:
  sudo wg set ${wg_iface} peer $(cat "${ACCESS_DIR}/${STUDENT_USER}_wireguard_public.key") remove
  sudo usermod -L ${STUDENT_USER}
  sudo systemctl disable --now oneduc-stagiaire-firewall.service
EOF

    chown "$OWNER_USER:$OWNER_USER" "$client_conf" "$readme"
    chmod 0600 "$client_conf" "$readme"
}

main() {
    require_root
    printf 'Starting trainee access setup...\n'
    printf 'Checking required commands...\n'
    need_cmd find
    need_cmd git
    need_cmd ip
    need_cmd iptables
    need_cmd php
    need_cmd runuser
    need_cmd ssh-keygen
    need_cmd systemctl
    need_cmd wg

    printf 'Checking source and owner...\n'
    [ -d "$SOURCE_DIR" ] || die "Source introuvable: ${SOURCE_DIR}"
    id "$OWNER_USER" >/dev/null 2>&1 || die "Utilisateur proprietaire introuvable: ${OWNER_USER}"

    printf 'Detecting WireGuard config...\n'
    if [ -z "$WG_CONF" ]; then
        WG_CONF="$(first_wireguard_conf)"
    fi
    printf 'WireGuard config candidate: %s\n' "${WG_CONF:-<none>}"
    [ -n "$WG_CONF" ] && [ -f "$WG_CONF" ] || die "Aucune config serveur WireGuard trouvee dans /etc/wireguard. J ai probablement trouve seulement une config cliente; il faut intervenir sur le serveur WireGuard, probablement louises.fr."
    assert_wireguard_server_conf

    local wg_iface
    wg_iface="$(basename "$WG_CONF" .conf)"

    if [ -z "$SERVER_VPN_IP" ]; then
        SERVER_VPN_IP="$(ip -4 -o addr show dev "$wg_iface" 2>/dev/null | awk '{print $4}' | cut -d/ -f1 | head -n 1 || true)"
    fi
    [ -n "$SERVER_VPN_IP" ] || SERVER_VPN_IP="10.8.0.1"

    printf 'Creating limited Linux user...\n'
    create_student_user
    local wg_public
    printf 'Creating access keys...\n'
    wg_public="$(create_access_material)"
    cleanup_invalid_wireguard_peer_reference

    existing_cidr="$(existing_peer_cidr "$wg_public")"
    if [ -n "$existing_cidr" ]; then
        use_student_cidr "$existing_cidr"
        printf 'Reusing existing WireGuard peer IP: %s\n' "$STUDENT_CIDR"
    elif grep -Eq "^[[:space:]]*AllowedIPs[[:space:]]*=[[:space:]]*${STUDENT_CIDR//./\\.}([[:space:],#]|$)" "$WG_CONF"; then
        printf 'WireGuard IP already used: %s\n' "$STUDENT_CIDR"
        choose_free_student_ip
    fi

    configure_student_ssh_access
    printf 'Adding WireGuard peer...\n'
    append_wireguard_peer "$wg_public"

    if wg show "$wg_iface" >/dev/null 2>&1; then
        wg set "$wg_iface" peer "$wg_public" allowed-ips "$STUDENT_CIDR"
    else
        systemctl restart "wg-quick@${wg_iface}.service"
    fi

    printf 'Preparing trainee workspace...\n'
    prepare_app_workspace
    printf 'Configuring Apache...\n'
    configure_apache
    printf 'Installing firewall restriction service...\n'
    write_firewall_script "$wg_iface"

    local server_public
    server_public="$(wg show "$wg_iface" public-key)"
    write_client_config_and_readme "$wg_iface" "$server_public" "$SERVER_VPN_IP"

    printf '\nOK: acces stagiaire pret.\n'
    printf 'Dossier codes pour test: %s\n' "$ACCESS_DIR"
    printf 'WireGuard client: %s/%s-wireguard.conf\n' "$ACCESS_DIR" "$STUDENT_USER"
    printf 'Cle SSH: %s/%s_oneduc_ed25519\n' "$ACCESS_DIR" "$STUDENT_USER"
    printf 'URL test VPN: http://%s:%s\n' "$SERVER_VPN_IP" "$APP_PORT"
}

main "$@"
