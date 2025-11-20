# Instruções para Agentes de IA - COWORKING-DIGITAL

## Visão Geral da Arquitetura

Este é um aplicativo **PHP moderno com sessões** que implementa um sistema de gerenciamento de espaço de coworking com autenticação baseada em email e recuperação de senha. A arquitetura segue um padrão **MVC simplificado** com separação clara entre API, apresentação e lógica.

### Estrutura Principal

- **`/api`**: Endpoints REST que retornam JSON (login, logout, registro, recuperação de senha)
- **`/public`**: Páginas HTML/PHP renderizadas no servidor (login, painel, esqueci_senha, etc.)
- **`/includes`**: Funções compartilhadas (validação, segurança, utilidades UI)
- **`/config`**: Configurações centralizadas e conexão com banco de dados

## Fluxos Críticos de Autenticação

### 1. **Fluxo de Login**
```
POST /api/login.php (JSON: email, senha)
  ↓
Valida email com filter_var() + trim
  ↓
password_verify() contra hash no DB
  ↓
Se primeiro acesso (precisa_redefinir_senha=1):
  → Gera código de 4 dígitos → Token em DB com expiração 15min
  → Retorna JSON com status PRIMEIRO_ACESSO
  ↓
Senão: Inicia sessão com iniciar_sessao($usuario)
  → Sessão armazena: id, nome, email, papel
```

**Arquivo chave**: `/api/login.php` (linhas 1-60+)

### 2. **Fluxo de Recuperação de Senha**
```
POST /api/senha/solicitar.php (JSON: email)
  ↓
Busca usuário ativo
  ↓
Invalida tokens antigos (UPDATE com WHERE usado_em IS NULL)
  ↓
Gera código 4 dígitos + registra em token_recuperacao_senha
  ↓
[DEBUG] Retorna código em JSON (remover em produção!)
```

**Padrão de token**: Sempre com expiração configurável em `CODIGO_EXPIRACAO_MINUTOS` (config.php = 15 min)

### 3. **Fluxo de Sessão Protegida**
```
proteger_pagina() em qualquer página que exija login
  ↓
Verifica se $_SESSION[SESSAO_USUARIO_KEY] existe
  ↓
Se não: limpa sessão + redireciona para login.php
```

**Exemplo**: `/public/painel.php` começa com `proteger_pagina()`

## Padrões e Convenções Projeto-Específicas

### Validação
- **Email**: Sempre usar `validar_email()` que normaliza com `strtolower() + trim()` + `filter_var(VALIDATE_EMAIL)`
- **Senha**: Mínimo 8 caracteres (verificado com `validar_senha()`)
- **Código recuperação**: 4 dígitos numéricos (verificado com `validar_codigo_recuperacao()`)
- **Formato**: Sempre retornar `false` ou `bool` nas funções de validação, nunca exceção

### Respostas JSON
Toda API segue padrão de resposta com estrutura:
```json
{
  "ok": true|false,
  "erro": "mensagem de erro",
  "mensagem": "mensagem de sucesso",
  [outros campos conforme operação]
}
```

Função `responder_erro()` automaticamente faz `exit`, então sempre coloque lógica antes da chamada.

### Proteção de Páginas
- **Páginas de autenticação** (login.php, registrar.php): Usam `proteger_autenticacao()` para redirecionar para painel se já logado
- **Páginas protegidas** (painel.php): Usam `proteger_pagina()` para exigir login

### Rate Limiting
- Simples: `aplicar_rate_limit()` faz `usleep(RATE_LIMIT_DELAY_MS * 1000)`
- Valor padrão: 800ms (configurável em config.php)
- Usado em: fluxos de falha (tentativas de login, etc.)

## Convenções de Código

### Banco de Dados
- **Conexão**: Sempre via `conectar_db()` que retorna `$pdo` com PDO::FETCH_ASSOC ativado
- **Prepared Statements**: Obrigatório para evitar SQL injection
- **Campos de usuário**: id, nome, email, senha_hash, papel_id, ativo, precisa_redefinir_senha
- **Tabela de tokens**: token_recuperacao_senha com campos: usuario_id, codigo, expira_em, usado_em

### Segurança
- **Hashing**: Usar `password_hash()` e `password_verify()` (não SHA256)
- **CORS**: Atualmente aberto (`*` em desenvolvimento), restringir em produção
- **Erro display**: Remover `ini_set('display_errors', 1)` em produção (vide `/api/login.php`)
- **Mensagens de erro**: Genéricas para usuário, detalhadas apenas em debug

### Arquivo de Configuração
Centralizado em `/config/config.php`:
```php
define('DB_HOST', 'localhost');
define('PERMITIR_CADASTRO', true);
define('CODIGO_EXPIRACAO_MINUTOS', 15);
define('RATE_LIMIT_DELAY_MS', 800);
define('SESSAO_USUARIO_KEY', 'usuario_logado');
```

Sempre verificar se a constante já está definida antes de usá-la em novas features.

## Instruções de Desenvolvimento

### Adicionando Novo Endpoint de API
1. Criar arquivo em `/api/novo_endpoint.php`
2. Começar com: `require_once 'funcoes_api.php';`
3. Validar entrada com funções em `/includes/funcoes.php`
4. Retornar JSON com padrão: `{'ok': bool, ...}`
5. Usar `responder_erro()` para erros (faz exit automaticamente)

### Adicionando Nova Página Protegida
1. Criar arquivo em `/public/nova_pagina.php`
2. Começar com: `require_once __DIR__ . '/../includes/seguranca.php';`
3. Chamar `proteger_pagina()` no início
4. Acessar dados do usuário via `$_SESSION[SESSAO_USUARIO_KEY]`

### Debug e Testing
- Código acadêmico retorna dados sensíveis em JSON (ex: `codigo_debug` em recuperação de senha)
- **Remover em produção**: Linhas com "PARA TESTE" ou "SÓ PARA DEBUG"
- Testar sempre com prepared statements e validação dupla

## Arquivos-Chave por Responsabilidade

| Arquivo | Responsabilidade |
|---------|------------------|
| `/config/config.php` | Constantes e configuração global |
| `/config/conexao.php` | Factory da conexão PDO |
| `/includes/funcoes.php` | Validação, rate limiting, respostas JSON |
| `/includes/seguranca.php` | Gestão de sessão, proteção de páginas |
| `/api/funcoes_api.php` | Parsing JSON, headers CORS |
| `/api/login.php` | Autenticação e início de sessão |
| `/public/login.php` | Renderização da página de login |
| `/public/painel.php` | Página protegida de exemplo |

## Pontos de Atenção para Agentes

1. **Sessões**: Sempre rodar `session_start()` antes de usar `$_SESSION` (já feito em seguranca.php)
2. **Redirecionamentos**: Usar `redirecionar()` função, nunca `header()` puro sem `exit`
3. **Encoding**: Banco usa `utf8mb4`, sempre verificar em novas queries
4. **Senhas**: Nunca logar, armazenar ou retornar senhas em plain text
5. **Expiração**: Tokens têm expiração em minutos, sempre verificar `expira_em > NOW()` nas queries
