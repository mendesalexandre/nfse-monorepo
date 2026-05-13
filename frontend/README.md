# Quasar Starter

Template base Quasar com visual shadcn/Tailwind para novos projetos.

## O que inclui

### Estilo global (`src/css/app.scss`)

Override do Quasar Framework para visual shadcn:

- Inputs, selects e textareas com altura automática (padding-based, não height fixo)
- Focus ring fino estilo shadcn (`box-shadow: 0 0 0 2px rgba($primary, 0.2)`)
- Botões sem uppercase, sem ripple, compactos (`min-height: 36px`)
- Tabs limpas com indicador fino de 2px, paleta slate
- Cards, notifications e dialogs com sombras Tailwind
- Notifications estilo soft (fundo claro, borda esquerda colorida, slide-in)
- Tooltip estilo DaisyUI (fundo slate-800, compacto)
- Header e drawer com shadow sutil (sem border)
- Toggle estilo pill switch compacto (32×18px)
- Checkbox compacto 16×16px
- Tabela com cabeçalho slate, zebra, hover suave
- Menu dropdown com shadow, borda sutil, items compactos
- Badge com variantes soft (fundo claro + texto colorido)
- Breadcrumbs com separador fino, último item bold
- Skeleton com fundo slate e wave animation
- Calendário (q-date) clean com border-radius 6px
- Paginação standalone compacta com border-radius 6px
- Accordion (expansion-item) com borda, hover suave
- Stepper clean com header compacto, dots 28px, sem ripple
- Chips soft com variantes de cor (como badges)
- Timeline clean com tipografia e linha fina
- Linear/Circular progress com border-radius pill
- Loading overlay com backdrop e card branco
- Dark mode completo (backgrounds, borders, tabelas, cards)
- Body com `background-color: #f5f5f5`

### Icon Set FontAwesome Light (`src/boot/fontawesome.js`)

- Todos os ícones internos do Quasar mapeados para `fa-light`
- Paginação, setas, chips, editor, stepper, uploader, datetime
- `font-weight: 400` (mais firme que o padrão 300)

### Paleta de cores (`src/css/quasar.variables.scss`)

Paleta Indigo/Moderno com contraste WCAG AA:

| Cor | Valor | Referência |
|---|---|---|
| `$primary` | `#ff7900` | Orange |
| `$secondary` | `#7c3aed` | Violet-600 |
| `$accent` | `#ec4899` | Pink-500 |
| `$positive` | `#16a34a` | Green-600 |
| `$negative` | `#dc2626` | Red-600 |
| `$warning` | `#d97706` | Amber-600 |
| `$info` | `#2563eb` | Blue-600 |

### Componentes globais (`src/boot/components.js`)

Todos registrados globalmente, disponíveis sem import:

| Componente | Descrição |
|---|---|
| `modal` | Dialog completo com tabs, maximizar, subtítulo, posição, rodapé |
| `v-label` | Label de formulário com obrigatório (`*`), tooltip e prop `uppercase` |
| `v-password` | Input senha com toggle de visibilidade (ícone fa-light) |
| `v-cpf` | Input com máscara CPF (`###.###.###-##`) |
| `v-cnpj` | Input com máscara CNPJ (`##.###.###/####-##`) |
| `v-telefone` | Input telefone com auto-detect fixo/celular (8 ou 9 dígitos) |
| `v-cep` | Input CEP com busca automática via ViaCEP |
| `v-date` | Input data com máscara DD/MM/YYYY e calendário popup (prop `picker`) |
| `v-money` | Input moeda R$ com máscara, prefix/suffix estilo Bootstrap input-group |
| `v-otp` | Input OTP com navegação por teclado |
| `v-qrcode` | Geração de QR code via canvas |
| `v-upload` | Drag & drop de arquivos com validação tipo/tamanho |
| `v-alert` | Alert soft estilo DaisyUI (info, success, warning, error) com `closable` |
| `v-confirm` | Dialog de confirmação (warning, danger, info) com loading |
| `v-status-badge` | Badge ativo/inativo com mapa de status customizável |
| `v-empty-state` | Estado vazio com ícone e mensagem |
| `v-autocomplete` | Select com campo de busca local e remota |
| `v-select-estado` | Select de estados brasileiros com bandeiras |
| `v-select-banco` | Select de bancos com logos |
| `v-data-table` | Wrapper q-table com search, empty state, loading e pagination |
| `v-form` | Wrapper formulário com `validate()` que valida todos os campos |
| `v-select-cidade` | Select de cidades BR via API IBGE, baseado no estado |
| `v-painel` | Painel lateral (side sheet) que desliza da direita ou esquerda, full-height |
| `v-page-header` | Cabeçalho de página com título, subtítulo, breadcrumbs e slot de ações |
| `v-stat-card` | Card de métrica com ícone, valor, tendência (up/down %) e prefixo/sufixo |
| `v-avatar` | Avatar com imagem ou iniciais fallback, status (online/offline/ausente) |
| `v-copy` | Texto clicável com botão copiar e feedback "Copiado!" |
| `v-timeline` | Timeline de atividades com ícone, autor, data e descrição |
| `v-filter` | Painel de filtros colapsável com badge de ativos e botão limpar |
| `v-checklist` | Checklist de etapas/onboarding com progresso, ícones e clique para concluir |
| `v-select` | Wrapper q-select com outlined dense, clearable, checkmark, emit-value e $attrs passthrough |

> `v-select-estado` e `v-select-banco` recebem dados via prop `options`.

### Composables (`src/composables/`)

| Composable | Descrição |
|---|---|
| `useNotify()` | Atalhos `success()`, `error()`, `warning()`, `info()` para notificações |
| `useApi()` | Wrapper com `loading`, `error` reativo e `request()` com tratamento automático |
| `useDarkMode()` | Toggle dark/light com persistência no localStorage |

### Utilitários (`src/utils/`)

| Arquivo | Funções |
|---|---|
| `date.js` | `toDisplay`, `toIso`, `isValidDate`, `today`, `todayDisplay`, `toDisplayDateTime`, `diffDays`, `addDays` |

### Layouts

- **MainLayout** — Header compacto (48px) + drawer lateral (60px) com ícones `fa-light`
- **AuthLayout** — Layout para páginas de autenticação

### Autenticação

- `/auth/login` — Login com layout two-column (branding + formulário)
- `/auth/esqueci-senha` — Recuperação de senha com card centralizado
- `/auth/criar-conta` — Registro com layout two-column
- **Auth store** (`src/stores/auth`) — login, logout, register, fetchUser com persistência localStorage
- **Axios interceptors** — token Bearer automático, redirect 401/403/500

### Páginas de erro

- `/erro/403` — Acesso negado
- `/erro/500` — Erro interno
- `404` — Página não encontrada (catch-all)

### Assets

- 30 SVGs de bandeiras de estados brasileiros
- 6 SVGs de logos de bancos

Documentação completa dos overrides CSS em `docs/app-scss.md`.

## Setup

```bash
npm install
```

### Desenvolvimento

```bash
quasar dev
```

### Build

```bash
quasar build
```
