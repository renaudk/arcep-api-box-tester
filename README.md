# Quick start

## Install dependencies with Composer
```shell
php composer.phar update
```

## Create and customize .env files
```shell
cp .env.example .cap.env
cp .env.example .staging.env 
cp .env.example .production.env  
```

## Create and customize JSON config files
```shell
cp api-credentials.json.example api-credentials.cap.json
cp api-credentials.json.example api-credentials.staging.json
cp api-credentials.json.example api-credentials.production.json
```

## Set HTTP Document Root to
```
<PROJECT>/public/
```

# IP Info service
## Setup 2 DNS entries, one A and one AAAA
```
ipv4.yourdomain.tld     IN  A       X.X.X.X
ipv6.yourdomain.tld     IN  AAAA    xx:xx:xx::y
```
## Setup HTTP server Document Root for these domains to 
```
<PROJECT>/ipinfo/
```
## Adapt `ipinfo.json` config file to your needs
```shell
cp ipinfo.json.example ipinfo.json
```