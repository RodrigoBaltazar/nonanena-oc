# 🐳 Exemplo de Uso com Docker

## Comandos Básicos

### 1. Iniciar o Aplicativo
```bash
docker-compose up -d
```

### 2. Ver Logs
```bash
docker-compose logs -f
```

### 3. Parar o Aplicativo
```bash
docker-compose down
```

### 4. Reconstruir (após mudanças)
```bash
docker-compose up --build -d
```

## Acessar o Aplicativo

- **URL:** http://localhost:8000
- **PWA:** Instalável no celular
- **PDF:** Geração automática de relatórios

## Estrutura Docker

```
nonanena-oc/
├── Dockerfile          # Configuração do container
├── docker-compose.yml  # Orquestração
├── .dockerignore       # Arquivos ignorados
├── start.sh           # Script de inicialização
└── data/              # Dados persistentes (SQLite)
```

## Volumes Persistentes

- `./data` → `/var/www/html/data` (banco SQLite)
- `./uploads` → `/var/www/html/uploads` (arquivos)

## Comandos Avançados

### Acessar Container
```bash
docker-compose exec app bash
```

### Ver Status
```bash
docker-compose ps
```

### Limpar Tudo
```bash
docker-compose down -v
docker system prune -f
```
