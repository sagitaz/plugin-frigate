# <u>Installation</u>
Ma procédure d'installation.

matériel : NUC intel i3-8109U

Proxmox 8.2.2

## Création d'une VM dédié

processeur : 2 cores 2 sockets
RAM : 8go
DD : 64go
USB : monter la clé Google Coral

Debian 12

## Installation Portainer

https://shape.host/resources/comment-installer-portainer-sur-un-serveur-debian-12


## stack frigate
> version: "3.9"
services:
  frigate:
    container_name: frigate
    restart: unless-stopped
    image: ghcr.io/blakeblackshear/frigate:stable
    devices:
      - /dev/bus/usb:/dev/bus/usb
      - /dev/dri/renderD128
    volumes:
      - ./config:/config
      - ./storage:/media/frigate
      - type: tmpfs # Optional: 1GB of memory, reduces SSD/SD Card wear
        target: /tmp/cache
        tmpfs:
          size: 1000000000
    ports:
      - "8971:8971"
      - "8554:8554" # RTSP feeds

      