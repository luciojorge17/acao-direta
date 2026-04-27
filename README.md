# Ação Direta - Sistema de Gestão de Ponto

Este é um sistema premium desenvolvido em Laravel para gestão de colaboradores e registro de ponto com auditoria.

## 🚀 Funcionalidades
- **Gestão de Usuários**: CRUD completo de administradores do sistema.
- **Gestão de Colaboradores**: Cadastro de funcionários com máscara de CPF e controle de status.
- **Registro de Ponto**: Lançamento de horários com justificativa obrigatória para edições e cancelamentos.
- **Relatórios**: Geração de relatórios agrupados por colaborador com exportação para PDF.

---

## 🐳 Rodando com Docker (Recomendado)

Se você tem o Docker instalado, este é o método mais rápido:

1. **Subir os containers:**
   ```bash
   docker-compose up -d
   ```

2. **Instalar dependências e configurar banco:**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan migrate --seed
   ```

3. **Acessar o sistema:**
   O sistema estará disponível em [http://localhost](http://localhost).

---

## 💻 Rodando Manualmente

Certifique-se de ter instalado: PHP 8.2+, Composer e um banco de dados (MySQL ou SQLite).

1. **Instalar dependências:**
   ```bash
   composer install
   ```

2. **Configurar o ambiente:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edite o `.env` para configurar suas credenciais de banco de dados.*

3. **Rodar Migrações e Seeds:**
   ```bash
   php artisan migrate --seed
   ```

4. **Iniciar o servidor:**
   ```bash
   php artisan serve
   ```
   Acesse [http://localhost:8000](http://localhost:8000).

---

## 🔐 Acesso Inicial

Utilize as credenciais abaixo para o primeiro login:

- **E-mail:** `admin@acaodireta.com.br`
- **Senha:** `Abc@1234`

---

## 🛠️ Tecnologias Utilizadas
- **Laravel 12**
- **Livewire** (Single File Components / Volt style)
- **MySQL**
- **DomPDF** (Exportação de relatórios)
- **jQuery** (Máscaras de entrada)
- **Vanilla CSS** (Design System customizado)

---
